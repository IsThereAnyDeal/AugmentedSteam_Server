<?php
declare(strict_types=1);

namespace AugmentedSteam\Server\Lib\OpenId;

/**
 * An implementation of OpenID authentication for Steam, adapted from xPaw's version:
 * {@link https://github.com/xPaw/SteamOpenID.php}
 */
class OpenIdProvider
{
    private const string ProviderLoginUrl = "https://steamcommunity.com/openid/login";

    private string $host;
    private string $returnUrl;

    public function __construct(string $host, string $returnPath) {
        $this->host = $host;
        $this->returnUrl = rtrim($host, "/")."/".ltrim($returnPath, "/");
    }

    public function isAuthenticationInProgress(): bool {
        return isset($_GET['openid_claimed_id']);
    }

    public function getAuthUrl(): string {
        return self::ProviderLoginUrl."?".http_build_query([
                "openid.identity" => "http://specs.openid.net/auth/2.0/identifier_select",
                "openid.claimed_id" => "http://specs.openid.net/auth/2.0/identifier_select",
                "openid.ns" => "http://specs.openid.net/auth/2.0",
                "openid.mode" => "checkid_setup",
                "openid.realm" => $this->host,
                "openid.return_to" => $this->returnUrl
            ]);
    }

    /**
     * Validates OpenID data, and verifies with Steam
     * @return ?string Returns the 64-bit SteamID if successful or null on failure
     */
    public function validateLogin(): ?string {
        // PHP automatically replaces dots with underscores in GET parameters
        // See https://www.php.net/variables.external#language.variables.external.dot-in-names
        if (filter_input(INPUT_GET, 'openid_mode') !== "id_res") {
            return null;
        }

        // See http://openid.net/specs/openid-authentication-2_0.html#positive_assertions
        $arguments = filter_input_array(INPUT_GET, [
            "openid_ns" => FILTER_SANITIZE_URL,
            "openid_op_endpoint" => FILTER_SANITIZE_URL,
            "openid_claimed_id" => FILTER_SANITIZE_URL,
            "openid_identity" => FILTER_SANITIZE_URL,
            "openid_return_to" => FILTER_SANITIZE_URL, // Should equal to url we sent
            "openid_response_nonce" => FILTER_SANITIZE_SPECIAL_CHARS,
            "openid_assoc_handle" => FILTER_SANITIZE_SPECIAL_CHARS, // Steam just sends 1234567890
            "openid_signed" => FILTER_SANITIZE_SPECIAL_CHARS,
            "openid_sig" => FILTER_SANITIZE_SPECIAL_CHARS
        ], true);

        if (!is_array($arguments)) {
            return null;
        }

        foreach ($arguments as $value) {
            // An array value will be FALSE if the filter fails, or NULL if the variable is not set.
            // In our case we want everything to be a string.
            if (!is_string($value)) {
                return null;
            }
        }

        if ($arguments['openid_claimed_id'] !== $arguments['openid_identity']
            || $arguments['openid_op_endpoint'] !== self::ProviderLoginUrl
            || $arguments['openid_ns'] !== "http://specs.openid.net/auth/2.0"
            || !is_string($arguments['openid_return_to'])
            || !is_string($arguments['openid_identity'])
            || strpos($arguments['openid_return_to'], $this->returnUrl) !== 0
            || preg_match("#^https?://steamcommunity.com/openid/id/(7656119\d{10})/?$#", $arguments['openid_identity'], $communityID) !== 1) {
            return null;
        }

        $arguments['openid_mode'] = "check_authentication";

        $c = curl_init();
        curl_setopt_array($c, [
            CURLOPT_USERAGENT => "OpenID Verification (+https://github.com/xPaw/SteamOpenID.php)",
            CURLOPT_URL => self::ProviderLoginUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $arguments,
        ]);

        $response = curl_exec($c);
        $code = curl_getinfo($c, CURLINFO_RESPONSE_CODE);

        if ($code !== 200 || !is_string($response)) {
            return null;
        }

        $keyValues = $this->parseKeyValues($response);
        if (($keyValues['ns'] ?? null) !== "http://specs.openid.net/auth/2.0") {
            return null;
        }

        if (($keyValues['is_valid'] ?? null) !== "true") {
            return null;
        }

        return $communityID[1];
    }

    /**
     * @return array<string, string>
     */
    private function parseKeyValues(string $response): array
    {
        // A message in Key-Value form is a sequence of lines. Each line begins with a key,
        // followed by a colon, and the value associated with the key. The line is terminated
        // by a single newline (UCS codepoint 10, "\n"). A key or value MUST NOT contain a
        // newline and a key also MUST NOT contain a colon.
        $responseLines = explode("\n", $response);
        $responseKeys = [];

        foreach($responseLines as $line) {
            $pair = explode(":", $line, 2);

            if (!isset($pair[1])) {
                continue;
            }

            list($key, $value) = $pair;
            $responseKeys[$key] = $value;
        }

        return $responseKeys;
    }
}
