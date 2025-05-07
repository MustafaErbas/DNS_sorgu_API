<?php
class DnsQuery {
    private $domain;

    public function __construct($domain) {
        $this->domain = $domain;
    }

    public function getARecords() {
        return dns_get_record($this->domain, DNS_A);
    }

    public function getMxRecords() {
        return dns_get_record($this->domain, DNS_MX);
    }

    public function getTxtRecords() {
        $records = dns_get_record($this->domain, DNS_TXT);
        $txtValues = array_map(function($record) {
            return $record['txt'];
        }, $records);
        return $txtValues;
    }

    public function getCnameRecords() {
        return dns_get_record($this->domain, DNS_CNAME);
    }

    public function getAaaaRecords() {
        return dns_get_record($this->domain, DNS_AAAA);
    }

    public function getSrvRecords() {
        return dns_get_record($this->domain, DNS_SRV);
    }

    public function getSoaRecords() {
        return dns_get_record($this->domain, DNS_SOA);
    }

    public function getSpfRecords() {
        $txtRecords = $this->getTxtRecords();
        $spfRecords = array_filter($txtRecords, function($txt) {
            return strpos($txt, 'v=spf1') === 0;
        });
        return $spfRecords;
    }

    public function getAllRecords() {
        // Kayıt türleri
        $recordTypes = [
            'A' => DNS_A,
            'MX' => DNS_MX,
            'TXT' => DNS_TXT,
            'CNAME' => DNS_CNAME,
            'AAAA' => DNS_AAAA,
            'SRV' => DNS_SRV,
            'SOA' => DNS_SOA
        ];

        // Tüm kayıtları depolayacak array
        $allRecords = [];

        // Her kayıt türü için sorgu yap ve sonuçları array'e ekle
        foreach ($recordTypes as $typeName => $typeValue) {
            $records = dns_get_record($this->domain, $typeValue);

            // TXT kayıtları için özel işleme
            if ($typeName === 'TXT') {
                $records = array_map(function($record) {
                    return $record['txt'];
                }, $records);
            }

            // Kayıt türüne göre array'e ekle veya uyarı mesajı ekle
            if (empty($records)) {
                $allRecords[$typeName] = ['message' => 'Kayıt bulunamadı.'];
            } else {
                $allRecords[$typeName] = $records;
            }
        }

        return $allRecords;
    }




}
?>
