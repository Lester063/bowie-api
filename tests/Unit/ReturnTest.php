<?php

namespace Tests\Unit;
use App\Http\Controllers\Api\ReturnController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Item;
use App\Models\Requests;
use App\Models\Returns;
use Illuminate\Http\Request;
use Tests\TestCase;

class ReturnTest extends TestCase
{
    use RefreshDatabase;
    protected $returnController;
    
    protected function setUp(): void {
        parent::setUp();
        $this->returnController = new ReturnController();
    }

    public function testIShouldBeAbleToReturnRequestedItem(): void
    {
        $item = Item::factory()->create([
            'isAvailable' => true
        ]);
        $user = User::factory()->create([
            'isAdmin' => false
        ]);
        $userAdmin = User::factory()->create([
            'isAdmin' => true
        ]);
        $requestItem = Requests::factory()->create([
            'idRequester' => (string)$user->id,
            'idItem' => $item->id,
            'statusRequest' => 'Approved'
        ]);

        $this->actingAs($user);
        $createRequest = Request::create('/api/return', 'POST',[
            'idRequest' => $requestItem->id
        ]);
        $response = $this->returnController->returnItem($createRequest);
        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Request return was sent successfully.');
        $this->assertTrue($responseData->data->idRequest === $requestItem->id);
        $this->assertTrue($responseData->data->idReturner === $user->id);
        $this->assertTrue($responseData->data->isApprove === false);
    }

    public function testIShouldNotBeAbleToReturnOtherUsersRequestedItem(): void
    {
        $item = Item::factory()->create([
            'isAvailable' => true
        ]);
        $user1 = User::factory()->create([
            'isAdmin' => false
        ]);
        $user2 = User::factory()->create([
            'isAdmin' => false
        ]);
        $requestItem = Requests::factory()->create([
            'idRequester' => (string)$user1->id,
            'idItem' => $item->id,
            'statusRequest' => 'Approved'
        ]);

        $this->actingAs($user2);
        $createRequest = Request::create('/api/return', 'POST',[
            'idRequest' => $requestItem->id
        ]);
        $response = $this->returnController->returnItem($createRequest);
        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'You cannot return this item.');
    }

    public function testIShouldNotBeAbleToReturnItemTwice(): void
    {
        $item = Item::factory()->create([
            'isAvailable' => true
        ]);
        $user = User::factory()->create([
            'isAdmin' => false
        ]);
        $requestItem = Requests::factory()->create([
            'idRequester' => (string)$user->id,
            'idItem' => $item->id,
            'statusRequest' => 'Approved'
        ]);
        $returnItem = Returns::factory()->create([
            'idRequest' => $requestItem->id,
            'idReturner' => $user->id,
            'isApprove' => false
        ]);

        $this->actingAs($user);
        $createRequest = Request::create('/api/return', 'POST',[
            'idRequest' => $requestItem->id
        ]);
        $response = $this->returnController->returnItem($createRequest);
        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'There is already a pending return for this request.');
    }

    public function testIShouldNotBeAbleToReturnItemWithInvalidRequestID(): void
    {
        $item = Item::factory()->create([
            'isAvailable' => true
        ]);
        $user = User::factory()->create([
            'isAdmin' => false
        ]);
        $this->actingAs($user);
        $createRequest = Request::create('/api/return', 'POST',[
            'idRequest' => 12
        ]);
        $response = $this->returnController->returnItem($createRequest);
        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Unable to find the request.');
    }

    public function testIShouldBeAbleToApproveReturnRequestOfUser(): void
    {
        $item = Item::factory()->create([
            'isAvailable' => true
        ]);
        $user = User::factory()->create([
            'isAdmin' => false
        ]);
        $requestItem = Requests::factory()->create([
            'idRequester' => (string)$user->id,
            'idItem' => $item->id,
            'statusRequest' => 'Approved'
        ]);
        $returnItem = Returns::factory()->create([
            'idRequest' => $requestItem->id,
            'idReturner' => $user->id,
            'isApprove' => false
        ]);

        $this->actingAs($user);
        $createRequest = Request::create('/api/return/{id}/approve', 'PUT',[
            'isApprove' => false,
        ]);
        $response = $this->returnController->approveReturn($createRequest, $returnItem->id);
        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Request for return has been approved.');
        $this->assertTrue($responseData->data->id === $returnItem->id);
        $this->assertTrue($responseData->data->isApprove === 1);
    }

    public function testIShouldNotBeAbleToApproveAnAlreadyApprovedReturnRequest(): void
    {
        $item = Item::factory()->create([
            'isAvailable' => true
        ]);
        $user = User::factory()->create([
            'isAdmin' => false
        ]);
        $requestItem = Requests::factory()->create([
            'idRequester' => (string)$user->id,
            'idItem' => $item->id,
            'statusRequest' => 'Approved'
        ]);
        $returnItem = Returns::factory()->create([
            'idRequest' => $requestItem->id,
            'idReturner' => $user->id,
            'isApprove' => true
        ]);

        $this->actingAs($user);
        $createRequest = Request::create('/api/return/{id}/approve', 'PUT',[
            'isApprove' => false,
        ]);
        $response = $this->returnController->approveReturn($createRequest, $returnItem->id);
        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Return request is already approved.');
    }

    public function testIShouldNotBeAbleToApproveAReturnRequestWithInvalidID(): void
    {
        $item = Item::factory()->create([
            'isAvailable' => true
        ]);
        $user = User::factory()->create([
            'isAdmin' => false
        ]);

        $this->actingAs($user);
        $createRequest = Request::create('/api/return/{id}/approve', 'PUT',[
            'isApprove' => false,
        ]);
        $response = $this->returnController->approveReturn($createRequest, 12);
        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Unable to find the return request.');
    }
}
