<?php
include "DNSlookup.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$domain = isset($_POST['domain']) ? trim($_POST['domain']) : '';
$queryType = isset($_GET['type']) ? strtoupper(trim($_GET['type'])) : '';

function domainChecker($domain)
{
    $pattern = '/[\/\\\\\?\[{\]\}\<\>\$\#]/';
    $domain = preg_replace($pattern,'',$domain);
    $explodedDomain = explode(".", $domain);
    $explodedCount = count($explodedDomain);
    $domainArr = array_slice($explodedDomain, 0);
    $domain = implode(".", $domainArr);
    if(empty($explodedDomain[$explodedCount-2])){
        return "++[ERROR_NOT_A_DOMAIN]++";
    }
    else
        return $domain;
}
$warnings = [];

// Domain doğrulaması
if (empty($domain)) {
    $warnings[] = 'Domain parametresi eksik.';
} elseif (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
    $domain =domainChecker($domain);
}



// Sorgu türü doğrulaması
$validQueries = ['A', 'MX', 'TXT', 'SPF', 'CNAME', 'AAAA', 'SRV', 'SOA','ALL'];
if (empty($queryType)) {
    $warnings[] = 'Sorgu türü belirtilmemiş.';
} elseif (!in_array($queryType, $validQueries)) {
    $warnings[] = 'Geçersiz sorgu türü.';
}


// Eğer uyarılar varsa, bunları JSON formatında döndür
if (!empty($warnings)) {
    echo json_encode(['warnings' => $warnings]);
    die();
}

// Uyarı yoksa sorguya devam et

$dnsQuery = new DnsQuery($domain);

switch ($queryType) {
    case "A":
        $dnsRecords = $dnsQuery->getARecords();
        break;
    case "MX":
        $dnsRecords = $dnsQuery->getMxRecords();
        break;
    case "TXT":
        $dnsRecords = $dnsQuery->getTxtRecords();
        break;
    case "SPF":
        $dnsRecords = $dnsQuery->getSpfRecords();
        break;
    case "CNAME":
        $dnsRecords = $dnsQuery->getCnameRecords();
        break;
    case "AAAA":
        $dnsRecords = $dnsQuery->getAaaaRecords();
        break;
    case "SRV":
        $dnsRecords = $dnsQuery->getSrvRecords();
        break;
    case "SOA":
        $dnsRecords = $dnsQuery->getSoaRecords();
        break;
    case "ALL":
        $dnsRecords = $dnsQuery->getAllRecords();
        break;
    default:
        echo json_encode(['error' => 'Bilinmeyen hata oluştu.']);
        die();
}

if (empty($dnsRecords)) {
    echo json_encode(['error' => 'Sorgulanan tür için kayıt bulunamadı.']);
    die();
}
// JSON formatında döndür
echo json_encode($dnsRecords,JSON_PRETTY_PRINT);
die();
?>