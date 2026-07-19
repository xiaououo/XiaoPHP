<?php
/**
 * 阿里云DNS管理类
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\app\tools;

use XiaoPHP\System\Config\Conf;

class AliyunDns
{
    private $accessKeyId;
    private $accessKeySecret;
    private $regionId = "cn-hangzhou";
    private $apiVersion = "2015-01-09";
    private $format = "JSON";
    private $signatureMethod = "HMAC-SHA1";
    private $signatureVersion = "1.0";

    function __construct()
    {
        $config = Conf::get("AliyunDns");
        $this->accessKeyId = $config["accessKeyId"] ?? "";
        $this->accessKeySecret = $config["accessKeySecret"] ?? "";
    }

    private function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    private function computeSignature($parameters, $secret)
    {
        ksort($parameters);
        $canonicalizedQueryString = "";
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .=
                "&" .
                $this->percentEncode($key) .
                "=" .
                $this->percentEncode($value);
        }
        $stringToSign =
            "GET&%2F&" .
            $this->percentEncode(substr($canonicalizedQueryString, 1));
        $signature = base64_encode(
            hash_hmac("sha1", $stringToSign, $secret . "&", true)
        );
        return $signature;
    }

    private function request($action, $params = [], $method = "GET")
    {
        if (empty($this->accessKeyId) || empty($this->accessKeySecret)) {
            return [
                "code" => "NO_CONFIG",
                "message" => "AccessKey not configured",
            ];
        }

        $publicParams = [
            "Format" => $this->format,
            "Version" => $this->apiVersion,
            "AccessKeyId" => $this->accessKeyId,
            "SignatureMethod" => $this->signatureMethod,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "SignatureVersion" => $this->signatureVersion,
            "SignatureNonce" => md5(uniqid(mt_rand(), true)),
            "RegionId" => $this->regionId,
        ];

        $allParams = array_merge($publicParams, ["Action" => $action], $params);

        ksort($allParams);
        $signature = $this->computeSignature(
            $allParams,
            $this->accessKeySecret
        );
        $allParams["Signature"] = $signature;

        $url = "https://alidns.aliyuncs.com/?" . http_build_query($allParams);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($allParams));
        }

        $output = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($output === false) {
            return ["code" => "CURL_ERROR", "message" => $error];
        }

        if ($httpCode != 200) {
            return [
                "code" => "HTTP_ERROR",
                "message" => "HTTP $httpCode: $output",
            ];
        }

        $result = json_decode($output, true);
        if (isset($result["Code"])) {
            return [
                "code" => $result["Code"],
                "message" => $result["Message"] ?? "",
            ];
        }

        return $result;
    }

    function list($domainName = null)
    {
        $params = [];
        if (!empty($domainName)) {
            $params["DomainName"] = $domainName;
        }
        $result = $this->request("DescribeDomainRecords", $params);

        if (isset($result["DomainRecords"]["Record"])) {
            return [
                "code" => 200,
                "data" => $result["DomainRecords"]["Record"],
                "total" =>
                    $result["TotalCount"] ??
                    count($result["DomainRecords"]["Record"]),
            ];
        }

        if (isset($result["Domains"]["Domain"])) {
            return [
                "code" => 200,
                "data" => $result["Domains"]["Domain"],
                "total" =>
                    $result["TotalCount"] ??
                    count($result["Domains"]["Domain"]),
            ];
        }

        return ["code" => 200, "data" => [], "total" => 0];
    }

    function domains()
    {
        $result = $this->request("DescribeDomains");
        if (isset($result["Domains"]["Domain"])) {
            return [
                "code" => 200,
                "data" => $result["Domains"]["Domain"],
                "total" =>
                    $result["TotalCount"] ??
                    count($result["Domains"]["Domain"]),
            ];
        }
        return ["code" => 200, "data" => [], "total" => 0];
    }

    function add($options)
    {
        $params = [
            "DomainName" => $options["domain"] ?? "",
            "RR" => $options["rr"] ?? ($options["RR"] ?? ""),
            "Type" => strtoupper(
                $options["record"] ?? ($options["type"] ?? "A")
            ),
            "Value" => $options["value"] ?? "",
            "TTL" => intval($options["ttl"] ?? ($options["TTL"] ?? 600)),
        ];

        if (isset($options["priority"]) || isset($options["Priority"])) {
            $params["Priority"] = intval(
                $options["priority"] ?? $options["Priority"]
            );
        }

        if (isset($options["line"]) || isset($options["Line"])) {
            $params["Line"] = $options["line"] ?? $options["Line"];
        }

        $result = $this->request("AddDomainRecord", $params);

        if (isset($result["RecordId"])) {
            return [
                "code" => 200,
                "message" => "Record added successfully",
                "data" => [
                    "recordId" => $result["RecordId"],
                    "requestId" => $result["RequestId"] ?? "",
                ],
            ];
        }

        return [
            "code" => $result["code"] ?? 500,
            "message" => $result["message"] ?? "Failed to add record",
        ];
    }

    function get($options)
    {
        $params = [
            "DomainName" => $options["domain"] ?? "",
            "RRKeyWord" => $options["rr"] ?? ($options["RR"] ?? ""),
            "TypeKeyWord" => isset($options["record"])
                ? strtoupper($options["record"])
                : (isset($options["type"])
                    ? strtoupper($options["type"])
                    : ""),
        ];

        $result = $this->request("DescribeDomainRecords", $params);

        if (
            isset($result["DomainRecords"]["Record"]) &&
            count($result["DomainRecords"]["Record"]) > 0
        ) {
            $records = $result["DomainRecords"]["Record"];
            if (count($records) == 1) {
                return [
                    "code" => 200,
                    "data" => $records[0],
                ];
            }
            return [
                "code" => 200,
                "data" => $records,
                "total" => count($records),
            ];
        }

        return [
            "code" => 404,
            "message" => "Record not found",
        ];
    }

    function update($options)
    {
        if (empty($options["recordId"]) && empty($options["record_id"])) {
            return [
                "code" => 400,
                "message" => "recordId is required for update",
            ];
        }

        $params = [
            "RecordId" => $options["recordId"] ?? ($options["record_id"] ?? ""),
        ];

        if (isset($options["rr"]) || isset($options["RR"])) {
            $params["RR"] = $options["rr"] ?? $options["RR"];
        }

        if (isset($options["record"]) || isset($options["type"])) {
            $params["Type"] = strtoupper(
                $options["record"] ?? $options["type"]
            );
        }

        if (isset($options["value"])) {
            $params["Value"] = $options["value"];
        }

        if (isset($options["ttl"]) || isset($options["TTL"])) {
            $params["TTL"] = intval($options["ttl"] ?? $options["TTL"]);
        }

        if (isset($options["priority"]) || isset($options["Priority"])) {
            $params["Priority"] = intval(
                $options["priority"] ?? $options["Priority"]
            );
        }

        if (isset($options["line"]) || isset($options["Line"])) {
            $params["Line"] = $options["line"] ?? $options["Line"];
        }

        $result = $this->request("UpdateDomainRecord", $params);

        if (isset($result["RecordId"])) {
            return [
                "code" => 200,
                "message" => "Record updated successfully",
                "data" => [
                    "recordId" => $result["RecordId"],
                ],
            ];
        }

        return [
            "code" => $result["code"] ?? 500,
            "message" => $result["message"] ?? "Failed to update record",
        ];
    }

    function del($options)
    {
        if (empty($options["recordId"]) && empty($options["record_id"])) {
            if (
                !empty($options["domain"]) &&
                (!empty($options["rr"]) || !empty($options["RR"]))
            ) {
                $record = $this->get($options);
                if (
                    $record["code"] == 200 &&
                    !empty($record["data"]["RecordId"])
                ) {
                    $options["recordId"] = $record["data"]["RecordId"];
                } else {
                    return ["code" => 404, "message" => "Record not found"];
                }
            } else {
                return [
                    "code" => 400,
                    "message" =>
                        "recordId or (domain + rr) is required for delete",
                ];
            }
        }

        $params = [
            "RecordId" => $options["recordId"] ?? $options["record_id"],
        ];

        $result = $this->request("DeleteDomainRecord", $params);

        if (isset($result["RecordId"])) {
            return [
                "code" => 200,
                "message" => "Record deleted successfully",
            ];
        }

        return [
            "code" => $result["code"] ?? 500,
            "message" => $result["message"] ?? "Failed to delete record",
        ];
    }

    function setStatus($options)
    {
        if (empty($options["recordId"]) && empty($options["record_id"])) {
            return [
                "code" => 400,
                "message" => "recordId is required",
            ];
        }

        $params = [
            "RecordId" => $options["recordId"] ?? $options["record_id"],
            "Status" => $options["status"] == "disable" ? "Disable" : "Enable",
        ];

        $result = $this->request("SetDomainRecordStatus", $params);

        if (isset($result["RecordId"])) {
            return [
                "code" => 200,
                "message" => "Status updated successfully",
            ];
        }

        return [
            "code" => $result["code"] ?? 500,
            "message" => $result["message"] ?? "Failed to update status",
        ];
    }

    function remark($options)
    {
        if (empty($options["recordId"]) && empty($options["record_id"])) {
            return [
                "code" => 400,
                "message" => "recordId is required",
            ];
        }

        $params = [
            "RecordId" => $options["recordId"] ?? $options["record_id"],
            "Remark" => $options["remark"] ?? "",
        ];

        $result = $this->request("UpdateDomainRecordRemark", $params);

        if (isset($result["RecordId"])) {
            return [
                "code" => 200,
                "message" => "Remark updated successfully",
            ];
        }

        return [
            "code" => $result["code"] ?? 500,
            "message" => $result["message"] ?? "Failed to update remark",
        ];
    }
}