<?php 


class JWT
{
    private $headers;

    private $secret;

    public function __construct()
    {
        $this->headers = [
            "alg" => "HS256",
            "typ"=> "JWT",
          "mfc"=> "HOYA",
          "cc"=> "nl",	
          "cn"=> "004790"
        ];
                // Intentionally not hardcoded. Provide via environment variables.
                $this->secret = getenv('ILOG_JWT_SECRET') ?: (getenv('JWT_SECRET') ?: '');
    }

    public function generate(array $payload): string
    {
        if ($this->secret === '') {
            throw new RuntimeException('Missing JWT secret (set ILOG_JWT_SECRET or JWT_SECRET).');
        }
        $headers = $this->encode(json_encode($this->headers)); // encode headers
        $payload["exp"] = time() + 60; // add expiration to payload
        $payload = $this->encode(json_encode($payload)); // encode payload
        $signature = hash_hmac('SHA256', "$headers.$payload", $this->secret, true); // create SHA256 signature
        $signature = $this->encode($signature); // encode signature

        return "$headers.$payload.$signature";
    }

    private function encode(string $str): string
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '='); // base64 encode string
    }

    public function is_valid(string $jwt): bool
    {
        if ($this->secret === '') {
            return false;
        }
        $token = explode('.', $jwt); // explode token based on JWT breaks
        if (!isset($token[1]) && !isset($token[2])) {
            return false; // fails if the header and payload is not set
        }
        $headers = base64_decode($token[0]); // decode header, create variable
        $payload = base64_decode($token[1]); // decode payload, create variable
        $clientSignature = $token[2]; // create variable for signature

        if (!json_decode($payload)) {
            return false; // fails if payload does not decode
        }

        if ((json_decode($payload)->exp - time()) < 0) {
            return false; // fails if expiration is greater than 0, setup for 1 minute
        }

        if (isset(json_decode($payload)->iss)) {
            if (json_decode($headers)->iss != json_decode($payload)->iss) {
                return false; // fails if issuers are not the same
            }
        } else {
            return false; // fails if issuer is not set 
        }

        if (isset(json_decode($payload)->aud)) {
            if (json_decode($headers)->aud != json_decode($payload)->aud) {
                return false; // fails if audiences are not the same
            }
        } else {
            return false; // fails if audience is not set
        }

        $base64_header = $this->encode($headers);
        $base64_payload = $this->encode($payload);

        $signature = hash_hmac('SHA256', $base64_header . "." . $base64_payload, $this->secret, true);
        $base64_signature = $this->encode($signature);

        return ($base64_signature === $clientSignature);
    }
}

?>