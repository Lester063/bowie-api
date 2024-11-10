<?php

namespace Tests\Unit;
use App\Models\User;
use App\Models\Item;
use Illuminate\Http\Request;
use Tests\TestCase;
use App\Http\Controllers\Api\RequestController;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequestsTest extends TestCase
{
    use RefreshDatabase;
    protected $requestsController;
    
    protected function setUp(): void {
        parent::setUp();
        $this->requestsController = new RequestController();
    }

    public function testRequestItemSuccess(): void
    {
        $item = Item::factory()->create([
            'isAvailable' => true
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);
        $request = Request::create('/api/requests', 'POST', [
            'idRequester' => (string)$user->id,
            'idItem' => (string)$item->id,
            'statusRequest' => 'Pending'
        ]);
        $response = $this->requestsController->store($request);

        $responseData = $response->getData();
        $data = json_decode($response->getContent(), true);

        $this->assertTrue($responseData->message === 'Request sent successfully.');
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('notification', $data);
    }

    public function testRequestItemIsNotAvailable(): void
    {
        $item = Item::factory()->create([
            'isAvailable' => false
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);
        $request = Request::create('/api/requests', 'POST', [
            'idRequester' => (string)$user->id,
            'idItem' => (string)$item->id,
            'statusRequest' => 'Pending'
        ]);
        $response = $this->requestsController->store($request);

        $responseData = $response->getData();

        $this->assertTrue($responseData->message === 'Item is not available.');

    }

    public function testRequestItemAllFieldsAreMissing(): void
    {
        $item = Item::factory()->create([
            'isAvailable' => false
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);
        $request = Request::create('/api/requests', 'POST', [
            'idRequester' => '',
            'idItem' => '',
            'statusRequest' => ''
        ]);
        $response = $this->requestsController->store($request);

        $responseData = $response->getData();

        $this->assertTrue($responseData->errors->idRequester[0] === 'The id requester field is required.');
        $this->assertTrue($responseData->errors->idItem[0] === 'The id item field is required.');
        $this->assertTrue($responseData->errors->statusRequest[0] === 'The status request field is required.');

    }

    public function testRequestItemUnableToFindItemId(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $request = Request::create('/api/requests', 'POST', [
            'idRequester' => (string)$user->id,
            'idItem' => '123',
            'statusRequest' => 'Pending'
        ]);
        $response = $this->requestsController->store($request);

        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->idItem[0] === 'The selected id item is invalid.');

    }
}
