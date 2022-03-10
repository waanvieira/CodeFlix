<?php

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;

trait TestSaves
{
    protected abstract function model();
    protected abstract function routeStore();
    protected abstract function routeUpdate();

    protected function assertStore(array $sendData, array $testDataBase, array $testJsonData = null): TestResponse
    {
        /** @var TestResponse $response */
        $response = $this->json('POST', $this->routeStore(), $sendData);
        if ($response->status() !== 201) {
            throw new \Exception("Response status must be 201, given {$response->status()}: \n {$response->content()}");
        }
        $this->assertInDataBase($response, $testDataBase);
        $this->assertJsonResponseContent($response, $testDataBase, $testJsonData);
        return $response;
    }

    protected function assertUpdate(array $sendData, array $testDataBase, array $testJsonData = null): TestResponse
    {
        /** @var TestResponse $response */
        $response = $this->json('PUT', $this->routeUpdate(), $sendData);
        if ($response->status() !== 200) {
            throw new \Exception("Response status must be 200, given {$response->status()}: \n {$response->content()}");
        }
        $this->assertInDataBase($response, $testDataBase);
        $this->assertJsonResponseContent($response, $testDataBase, $testJsonData);
        return $response;
    }

    private function assertInDataBase(TestResponse $response, array $testDataBase)
    {
        $model = $this->model();
        $table = (new $model)->getTable();
        $this->assertDataBaseHas($table, $testDataBase + ['id' => $response->json('id')]);
    }

    private function assertJsonResponseContent(TestResponse $response, array $testJsonData, array $testDataBase = null)
    {
        $testResponse = $testJsonData ?? $testDataBase;
        $response->assertJsonFragment($testResponse + ['id' => $response->json('id')]);
    }
}