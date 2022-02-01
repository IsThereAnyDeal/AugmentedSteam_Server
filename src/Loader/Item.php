<?php
namespace AugmentedSteam\Server\Loader;

final class Item
{
    private string $id;
    private string $method = "GET";
    private string $url;
    private array $curlOptions = [];
    private bool $allowRedirect = true;
    private ?string $body = null;
    private array $headers = [];
    private array $data = [];

    private int $attempt = 1;

    public function __construct(string $url) {
        $this->id = uniqid("", true);
        $this->url = $url;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function setMethod(string $method): self {
        $this->method = $method;
        return $this;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function setUrl(string $url): self {
        $this->url = $url;
        return $this;
    }

    public function getCurlOptions(): array {
        return $this->curlOptions;
    }

    public function setCurlOptions(array $options): self {
        $this->curlOptions = $options;
        return $this;
    }

    public function isAllowRedirect(): bool {
        return $this->allowRedirect;
    }

    public function setAllowRedirect(bool $allowRedirect): self {
        $this->allowRedirect = $allowRedirect;
        return $this;
    }

    public function getBody(): ?string {
        return $this->body;
    }

    public function setBody(?string $body): self {
        $this->body = $body;
        return $this;
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function setHeaders(array $headers): self {
        $this->headers = $headers;
        return $this;
    }

    public function getData(): array {
        return $this->data;
    }

    public function setData(array $data): self {
        $this->data = $data;
        return $this;
    }

    public function getAttempt(): int {
        return $this->attempt;
    }

    public function setAttempt(int $attempt): self {
        $this->attempt = $attempt;
        return $this;
    }

    public function incrementAttempt(): self {
        $this->attempt += 1;
        return $this;
    }
}
