<?php

namespace Tests\Unit;
use App\Models\User;
use App\Models\Item;
use App\Models\Requests;
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

    public function testActionRequestUnableToFindRequest(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'isDeleted' => false,
            'isAvailable' => true
        ]);
        $data = Request::create('/api/requests', 'POST', [
            'action' => 'Approving'
        ]);
        $requests = Requests::factory()->create([
            'idItem' => $item->id
        ]);
        $this->actingAs($user);
        $response = $this->requestsController->actionRequest($data, '123123');

        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Unable to find the request.');

    }

    public function testActionRequestWhenRequestIsAlreadyClosed(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'isDeleted' => false,
            'isAvailable' => false
        ]);
        $data = Request::create('/api/requests', 'POST', [
            'action' => 'Approving'
        ]);
        $requests = Requests::factory()->create([
            'idItem' => $item->id,
            'statusRequest' => 'Closed'
        ]);
        $this->actingAs($user);
        $response = $this->requestsController->actionRequest($data, $requests->id);

        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Unable to make any action.');

    }

    public function testActionRequestWhenItemIsDeleted(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'isAvailable' => false,
            'isDeleted' => true
        ]);
        $data = Request::create('/api/requests', 'POST', [
            'action' => 'Approving'
        ]);
        $requests = Requests::factory()->create([
            'idItem' => $item->id,
        ]);
        $this->actingAs($user);
        $response = $this->requestsController->actionRequest($data, $requests->id);

        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Item is deleted.');

    }

    public function testActionRequestWhenItemIsNotAvailable(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'isDeleted' => false,
            'isAvailable' => false,
        ]);
        $data = Request::create('/api/requests', 'POST', [
            'action' => 'Approving'
        ]);
        $requests = Requests::factory()->create([
            'idItem' => $item->id,
        ]);
        $this->actingAs($user);
        $response = $this->requestsController->actionRequest($data, $requests->id);

        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Item is not available at the moment.');

    }

    public function testOtherPendingRequestShouldBeClosedWhenRequestWithTheSameItemWasApproved(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'isDeleted' => false,
            'isAvailable' => true,
        ]);
        $requestItem1 = Requests::factory()->create([
            'idItem' => $item->id,
            'statusRequest' => 'Pending'
        ]);
        $requestItem2 = Requests::factory()->create([
            'idItem' => $item->id,
            'statusRequest' => 'Pending'
        ]);
        $data = Request::create('/api/requests', 'POST', [
            'action' => 'Approving'
        ]);

        $this->actingAs($user);
        $response = $this->requestsController->actionRequest($data, $requestItem1->id);

        $responseData = $response->getData();

        $this->assertTrue($responseData->message === 'Request was approved.');

        $this->assertDatabaseHas('requests', [
            'id' => $requestItem2->id,
            'statusRequest' => 'Closed',
        ]);
        $this->assertDatabaseHas('requests', [
            'id' => $requestItem1->id,
            'statusRequest' => 'Approved',
        ]);

    }

    public function testMessageShouldBeSentForOtherRequestWhenRequestWithTheSameItemWasApproved(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'isDeleted' => false,
            'isAvailable' => true,
        ]);
        $requestItem1 = Requests::factory()->create([
            'idItem' => $item->id,
            'statusRequest' => 'Pending'
        ]);
        $requestItem2 = Requests::factory()->create([
            'idItem' => $item->id,
            'statusRequest' => 'Pending'
        ]);
        $data = Request::create('/api/requests', 'POST', [
            'action' => 'Approving'
        ]);

        $this->actingAs($user);
        $response = $this->requestsController->actionRequest($data, $requestItem1->id);

        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Request was approved.');

        $this->assertDatabaseHas('request_communications', [
            'idSender' => $user->id,
            'idRequest' => $requestItem2->id,
            'message' => 'The item you have requested has been processed to other User. 
                                 Therefore, this request will be closed, thank you.'
        ]);

    }

    public function testNotificationShouldBeSentForUserWithRequestWhenRequestWithTheSameItemWasApproved(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'isDeleted' => false,
            'isAvailable' => true,
        ]);
        $requestItem1 = Requests::factory()->create([
            'idItem' => $item->id,
            'statusRequest' => 'Pending'
        ]);
        $requestItem2 = Requests::factory()->create([
            'idItem' => $item->id,
            'statusRequest' => 'Pending'
        ]);
        $data = Request::create('/api/requests', 'POST', [
            'action' => 'Approving'
        ]);

        $this->actingAs($user);
        $response = $this->requestsController->actionRequest($data, $requestItem1->id);

        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Request was approved.');

        $this->assertDatabaseHas('notifications', [
            'senderUserId' => $user->id,
            'type' =>  'close the request',
            'typeValueId' => $requestItem1->id,
            'notificationMessage' => $user->firstName.' close the request of item '.$item->itemCode.'.'
        ]);
    }

    public function testMessageShouldBeSentToTheRequesterWhenItemRequestIsApproved(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'isDeleted' => false,
            'isAvailable' => true,
        ]);
        $requestItem = Requests::factory()->create([
            'idItem' => $item->id,
            'statusRequest' => 'Pending'
        ]);

        $data = Request::create('/api/requests', 'POST', [
            'action' => 'Approving'
        ]);

        $this->actingAs($user);
        $response = $this->requestsController->actionRequest($data, $requestItem->id);

        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Request was approved.');

        $this->assertDatabaseHas('request_communications', [
            'idSender' => $user->id,
            'idRequest' => $requestItem->id,
            'message' => 'Your request for this item has been approved.'
        ]);

    }

    public function testNotificationShouldBeSentToTheRequesterWhenItemRequestIsApproved(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'isDeleted' => false,
            'isAvailable' => true,
        ]);
        $requestItem = Requests::factory()->create([
            'idItem' => $item->id,
            'statusRequest' => 'Pending'
        ]);
        $data = Request::create('/api/requests', 'POST', [
            'action' => 'Approving'
        ]);

        $this->actingAs($user);
        $response = $this->requestsController->actionRequest($data, $requestItem->id);

        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Request was approved.');

        $this->assertDatabaseHas('notifications', [
            'senderUserId' => $user->id,
            'type' =>  'approve the request',
            'typeValueId' => $requestItem->id,
            'notificationMessage' => $user->firstName.' approve the request of item '.$item->itemCode.'.'
        ]);
    }

    public function testRequestedItemAvailabilityWillBeSetToFalseWhenItemIsApproved(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'isDeleted' => false,
            'isAvailable' => true,
        ]);
        $requestItem = Requests::factory()->create([
            'idItem' => $item->id,
            'statusRequest' => 'Pending'
        ]);
        $data = Request::create('/api/requests', 'POST', [
            'action' => 'Approving'
        ]);

        $this->actingAs($user);
        $response = $this->requestsController->actionRequest($data, $requestItem->id);

        $responseData = $response->getData();
        $this->assertTrue($responseData->message === 'Request was approved.');

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'itemCode' => $item->itemCode,
            'isAvailable' => false
        ]);
    }
}
