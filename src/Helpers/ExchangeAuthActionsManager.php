<?php

namespace GIS\CatalogExchange\Helpers;

use App\Models\User;
use GIS\CatalogExchange\Facades\ExchangeProtocolActions;
use GIS\CatalogExchange\Interfaces\CmlInterface;
use GIS\CatalogExchange\Models\Cml;
use Illuminate\Support\Facades\Auth;

class ExchangeAuthActionsManager
{
    const COOKIE_NAME = "catalogExchangeProtocol";

    public function modeCheckAuth(): string
    {
        if (! $this->checkRequestUser()) { return ExchangeProtocolActions::failure("Unauthorized"); }

        $user = Auth::user();
        /**
         * @var User $user
         */
        if (! $user->can("app-management")) { return ExchangeProtocolActions::failure("Access denied"); }

        $modelClass = config("catalog-exchange.customCmlModel") ?? Cml::class;
        $cml = $modelClass::query()->create();
        /**
         * @var CmlInterface $cml
         */
        return ExchangeProtocolActions::answer(implode("\n", [
            "success",
            self::COOKIE_NAME,
            $cml->id,
        ]));
    }

    public function checkRequestUser(): bool
    {
        return Auth::attempt([
            "email" => request()->getUser(),
            "password" => request()->getPassword()
        ]);
    }

    public function getUserCookie(): string|CmlInterface
    {
        $cookie = request()->cookie(self::COOKIE_NAME);
        if (! $cookie) { return ExchangeProtocolActions::failure("Authorize cookie not found."); }
        $modelClass = config("catalog-exchange.customCmlModel") ?? Cml::class;
        $cml = $modelClass::query()
            ->where("id", $cookie)
            ->first();
        if (! $cml) { return ExchangeProtocolActions::failure("The cookie value is incorrect."); }
        return $cml;
    }
}
