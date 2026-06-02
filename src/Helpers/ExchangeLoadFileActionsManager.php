<?php

namespace GIS\CatalogExchange\Helpers;

class ExchangeLoadFileActionsManager
{
    public function getAvailableFileTypes(): array
    {
        return ["import.xml", "offers.xml", "yml.xml", "archive.zip",];
    }

    public function getFileTypeByName(string $name): string
    {
        if (str_contains($name, "yml")) { return "yml"; }
        if (str_contains($name, "import")) { return "import"; }
        if (str_contains($name, "offers")) { return "offers"; }
        if (str_contains($name, ".zip")) { return "zip"; }
        return "undefined";
    }
}
