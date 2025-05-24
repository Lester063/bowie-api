<?php

namespace Tests\Unit;
use App\Http\Controllers\Api\RequestCommunicationController;
use App\Models\Item;
use App\Models\Requests;
use App\Models\User;
use App\Models\RequestCommunication;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RequestCommunicationTest extends TestCase
{
    use RefreshDatabase;
    protected $requestCommunicationController;

    protected function setUp():  void {
        parent::setUp();
        $this->requestCommunicationController = new RequestCommunicationController();
    }

    public function testIShouldNotBeAbleToSendAMessageOnOtherUsersRequest(): void
    {
        $item = Item::factory()->create();
        $requests = Requests::factory()->create([
            'idRequester' => 10
        ]);
        $user = User::factory()->create([
            'isAdmin' => false
        ]);
        $this->actingAs($user);
        $createRequest = Request::create('/api/requestcommunication', 'POST',[
            'idRequest' => (string)$requests->id,
            'message' => 'Hey!'
        ]);
        $response = $this->requestCommunicationController->store($createRequest);
        $responseData = $response->getData();
        $this->assertTrue($responseData->error === 'You are not allowed to send a message with this request.');
        $this->assertDatabaseMissing('request_communications', [
            'idRequest' => (string)$requests->id,
            'message' => $createRequest->message,
        ]);
    }

    public function testErrorShouldBeDisplayedWhenSendingAMessageOnANonExistingRequest(): void
    {
        $item = Item::factory()->create();
        $requests = Requests::factory()->create([
            'idRequester' => 10
        ]);
        $user = User::factory()->create([
            'isAdmin' => false
        ]);
        $this->actingAs($user);
        $createRequest = Request::create('/api/requestcommunication', 'POST',[
            'idRequest' => '22',
            'message' => 'Hey!'
        ]);
        $response = $this->requestCommunicationController->store($createRequest);
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->idRequest[0] === 'The selected id request is invalid.');
    }

    public function testErrorMessageShouldBeDisplayedWhenExceedingTheMaxNumberOfCharacter(): void
    {
        $item = Item::factory()->create();
        $requests = Requests::factory()->create([
            'idRequester' => 10
        ]);
        $user = User::factory()->create([
            'isAdmin' => false
        ]);
        $this->actingAs($user);
        $createRequest = Request::create('/api/requestcommunication', 'POST',[
            'idRequest' => (string)$requests->id,
            'message' => 'SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage SampleMessage '
        ]);
        $response = $this->requestCommunicationController->store($createRequest);
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->message[0] === 'The message field must not be greater than 255 characters.');
    }
}
