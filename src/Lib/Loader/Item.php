<?php
namespace AugmentedSteam\Server\Lib\Loader;

final class Item
{
    private string $id;
    private string $method = "GET";
    private string $url;
    /** @var array<int, mixed> */
    private array $curlOptions = [];
    private bool $allowRedirect = true;
    private ?string $body = null;
    /** @var array<string, mixed> */
    private array $headers = [];
    /** @var array<mixed> */
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

    /**
     * @return array<int, mixed>
     */
    public function getCurlOptions(): array {
        return $this->curlOptions;
    }

    /**
     * @param array<int, mixed> $options
     */
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

    /**
     * @return array<mixed>
     */
    public function getHeaders(): array {
        return $this->headers;
    }

    /**
     * @param array<mixed> $headers
     */
    public function setHeaders(array $headers): self {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * @param array<mixed> $data
     */
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
