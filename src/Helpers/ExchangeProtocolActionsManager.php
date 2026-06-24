<?php

namespace GIS\CatalogExchange\Helpers;

use GIS\CatalogExchange\Facades\ExchangeAuthActions;
use GIS\CatalogExchange\Facades\ExchangeLoadFileActions;
use GIS\CatalogExchange\Facades\ExchangeParserActions;

class ExchangeProtocolActionsManager
{
    protected string|null $type = null;
    protected string|null $mode = null;

    public function __construct()
    {
        $this->type = request()->input('type');
        $this->mode = request()->input('mode');
    }

    public function init(): string
    {
        if (! $this->type || ! $this->mode) { $this->failure("Not enough params"); }

        if ($this->mode == "checkauth") { return ExchangeAuthActions::modeCheckAuth(); }

        $cml = ExchangeAuthActions::getUserCookie();
        if (is_string($cml)) { return $cml; }

        return match ($this->mode) {
            "init" => $this->answer(implode("\n", [
                "zip=" . config("catalog-exchange.zip", "no"),
                "file_limit=" . config("catalog-exchange.fileLimit", "95000000"),
            ])),
            "file" => ExchangeLoadFileActions::modeLoadFile($cml),
            "import" => ExchangeParserActions::checkFileProgress($cml),
            default => $this->failure("Undefined mode"),
        };
    }

    public function failure(string $details = ""): string
    {
        $value = "failure" . (empty($details) ? "" : "\n$details\n");
        return $this->answer($value);
    }

    public function answer(string $value): string
    {
        return iconv("UTF-8", "windows-1251", "$value\n");
    }
}
