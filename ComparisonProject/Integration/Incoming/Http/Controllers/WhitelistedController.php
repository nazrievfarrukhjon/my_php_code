<?php

namespace App\Comparison\Integration\Incoming\Http\Controllers;

use App\Comparison\UseCases\WhiteVsBlackComparatorUseCase;
use App\MembersOfComparison\Whitelisted\Entities\Whitelisted;

class WhitelistedController {


    public function find($body): void
    {
        $bl = new WhiteVsBlackComparatorUseCase();
        echo json_encode($bl->findByName($body));
    }

    public function getAll(): void
    {
        echo 'About Page';
    }

    public function getById(int $id): void
    {
        echo 'About Page';
    }

    public function update(array $params): false|string
    {
        return json_encode(Whitelisted::update($params));
    }

    public function delete(array $params): bool
    {
        return Whitelisted::delete($params['id']);
    }

    public function index(): void
    {
        echo 'welcome';
    }

    public function save(array $params): false|int
    {
        return Whitelisted::create($params);
    }

    public function filter(array $params): string
    {
        $groupByParams = [
            $params['group_by'][0]
        ];
        return Whitelisted::query()
            ->toSql();
    }

}
