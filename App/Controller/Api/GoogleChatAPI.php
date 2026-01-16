<?php

namespace App\Controller\Api;

class GoogleChatAPI
{

    public static function sendMessage($text, $number)
    {
        $payload = [
            "cards" => [
                [
                    "header" => [
                        "title" => "⏱️ <b>AGENDADOS</b> ⏱️",
                        "subtitle" => ""
                    ],
                    "sections" => [
                        [
                            "widgets" => [
                                [
                                    "textParagraph" => [
                                        "text" => $text
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $headers = [
            "Content-Type: application/json"
        ];

        $ch = curl_init($number);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        curl_setopt($ch, CURLOPT_STDERR, fopen('php://stderr', 'w'));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            return ["error" => "Erro cURL: " . curl_error($ch)];
        }


        if ($httpCode === 200) {
            return true;
        }

        return [
            "error" => "Erro HTTP {$httpCode}",
            "response" => $response
        ];
    }
}