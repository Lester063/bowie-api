<?php

namespace Tests\Unit;
use App\Models\Notification;
use App\Models\Requests;
use App\Models\User;
use App\Models\Item;
use App\Models\Returns;
use Tests\TestCase;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;
    protected $notificationController;

    protected function setUp(): void {
        parent::setUp();

        $this->notificationController = new NotificationController;
    }

    public function testSendNotification() {
        $notification = $this->notificationController->sendNotification($data = [
            'recipientUserId' => '1',
            'senderUserId' => '2',
            'type' => 'ehehe',
            'notificationMessage' => 'Lester is requesting the item OGE.',
            'isRead' => 0,
            'typeValueId' => '123'
        ]);

        //Assert: Check if the notification was created and has the correct data
        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertDatabaseHas('notifications', [
            'recipientUserId' => $data['recipientUserId'],
            'senderUserId' => $data['senderUserId'],
            'type' => $data['type'],
            'notificationMessage' => $data['notificationMessage'],
            'isRead' => $data['isRead'],
            'typeValueId' => $data['typeValueId'],
        ]);

    }

    public function testGenerateMessageIsInvalid() {
        $notificationMessage = $this->notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'invalid message',
            'requestID' => '1'
        ]);
        $this->assertTrue($notificationMessage == 'Invalid');
    }

    public function testGenerateMessageForRequestingTheItem() {
        $notificationMessage = $this->notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'requesting the item',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester is requesting the item OGE.');
    }

    public function testGenerateMessageForReturningTheItem() {
        $notificationMessage = $this->notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'returning the item',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester is returning the item OGE.');
    }

    public function testGenerateMessageForApproveTheRequest() {
        $notificationMessage = $this->notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'approve the request',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester approve the request of item OGE.');
    }

    public function testGenerateMessageForCloseTheRequest() {
        $notificationMessage = $this->notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'close the request',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester close the request of item OGE.');
    }

    public function testGenerateMessageForDeclineTheRequest() {
        $notificationMessage = $this->notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'decline the request',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester decline the request of item OGE.');
    }

    public function testGenerateMessageForApproveTheReturn() {
        $notificationMessage = $this->notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'approve the return',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester approve the return of item OGE.');
    }

    public function testGenerateMessageForSentMessage() {
        $notificationMessage = $this->notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'sent a message',
            'requestID' => '1'
        ]);

        $this->assertTrue($notificationMessage == 'Lester sent a message on the request item with Reference #001.');
    }

    public function testReadUnreadUserNotification() {
        $user = User::factory()->create();
        $this->actingAs($user);

        $notification = Notification::factory()->create([
            'recipientUserId' => $user['id'],
            'senderUserId' => '2',
            'type' => 'ehehe',
            'notificationMessage' => 'Lester is requesting the item OGE.',
            'isRead' => 0,
            'typeValueId' => '123'
        ]);
        $this->assertTrue($notification['isRead'] == false);

        $this->notificationController->readUnreadUserNotification();

        $this->assertDatabaseHas('notifications', [
            'recipientUserId' => $user['id'],
            'senderUserId' => '2',
            'type' => 'ehehe',
            'notificationMessage' => 'Lester is requesting the item OGE.',
            'isRead' => true,
            'typeValueId' => '123'
        ]);

    }

    public function testRegenerateNotificationMessageIsInvalid() {
        $user = User::factory()->create();
        $this->actingAs($user);

        $notification = Notification::factory()->create([
            'recipientUserId' => $user['id'],
            'senderUserId' => '2',
            'type' => 'ehehe',
            'notificationMessage' => 'Sample User is requesting the item OGE.',
            'isRead' => 0,
            'typeValueId' => '123'
        ]);

        $regeneratedMessage = $this->notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == 'Invalid');

    }

    public function testRegenerateNotificationForRequestingTheItem() {
        $user1 = User::factory()->create([
            'firstName' => 'Sample User'
        ]);
        $user2 = User::factory()->create([
            'firstName' => 'Lester'
        ]);
        $this->actingAs($user2);
        
        $item = Item::factory()->create([
            'itemName' => 'Monitorr',
            'itemCode' => 'MRR1',
        ]);

        $requests = Requests::factory()->create([
            'idRequester' => $user1['id'],
            'idItem' => $item['id'],
        ]);

        $notification = Notification::factory()->create([
            'recipientUserId' => $user2['id'],
            'senderUserId' => $user1['id'],
            'type' => 'requesting the item',
            'notificationMessage' => 'Sample User is requesting the item MRR1.',
            'isRead' => 0,
            'typeValueId' => $requests['id']
        ]);

        $regeneratedMessage = $this->notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

    }

    public function testRegenerateNotificationForApproveTheRequest() {
        $user1 = User::factory()->create([
            'firstName' => 'Sample User'
        ]);
        $user2 = User::factory()->create([
            'firstName' => 'Lester'
        ]);
        $this->actingAs($user1);
        
        $item = Item::factory()->create([
            'itemName' => 'Monitorr',
            'itemCode' => 'MRR1',
        ]);

        $requests = Requests::factory()->create([
            'idRequester' => $user1['id'],
            'idItem' => $item['id'],
        ]);

        $notification = Notification::factory()->create([
            'recipientUserId' => $user1['id'],
            'senderUserId' => $user2['id'],
            'type' => 'approve the request',
            'notificationMessage' => 'Lester approve the request of item MRR1.',
            'isRead' => 0,
            'typeValueId' => $requests['id']
        ]);

        $regeneratedMessage = $this->notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

    }

    public function testRegenerateNotificationForCloseTheRequest() {
        $user1 = User::factory()->create([
            'firstName' => 'Sample User'
        ]);
        $user2 = User::factory()->create([
            'firstName' => 'Lester'
        ]);
        $this->actingAs($user1);
        
        $item = Item::factory()->create([
            'itemName' => 'Monitorr',
            'itemCode' => 'MRR1',
        ]);

        $requests = Requests::factory()->create([
            'idRequester' => $user1['id'],
            'idItem' => $item['id'],
        ]);

        $notification = Notification::factory()->create([
            'recipientUserId' => $user1['id'],
            'senderUserId' => $user2['id'],
            'type' => 'close the request',
            'notificationMessage' => 'Lester close the request of item MRR1.',
            'isRead' => 0,
            'typeValueId' => $requests['id']
        ]);

        $regeneratedMessage = $this->notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

    }

    public function testRegenerateNotificationForDeclineTheRequest() {
        $user1 = User::factory()->create([
            'firstName' => 'Sample User'
        ]);
        $user2 = User::factory()->create([
            'firstName' => 'Lester'
        ]);
        $this->actingAs($user1);
        
        $item = Item::factory()->create([
            'itemName' => 'Monitorr',
            'itemCode' => 'MRR1',
        ]);

        $requests = Requests::factory()->create([
            'idRequester' => $user1['id'],
            'idItem' => $item['id'],
        ]);

        $notification = Notification::factory()->create([
            'recipientUserId' => $user1['id'],
            'senderUserId' => $user2['id'],
            'type' => 'decline the request',
            'notificationMessage' => 'Lester decline the request of item MRR1.',
            'isRead' => 0,
            'typeValueId' => $requests['id']
        ]);

        $regeneratedMessage = $this->notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

    }

    public function testRegenerateNotificationForReturningTheItem() {
        $user1 = User::factory()->create([
            'firstName' => 'Sample User'
        ]);
        $user2 = User::factory()->create([
            'firstName' => 'Lester'
        ]);
        $this->actingAs($user1);
        
        $item = Item::factory()->create([
            'itemName' => 'Monitorr',
            'itemCode' => 'MRR1',
        ]);

        $requests = Requests::factory()->create([
            'idRequester' => $user1['id'],
            'idItem' => $item['id'],
        ]);

        $returns = Returns::factory()->create([
            'idRequest' => $requests['id'],
            'idReturner' => $requests['idRequester']
        ]);

        $notification = Notification::factory()->create([
            'recipientUserId' => $user2['id'],
            'senderUserId' => $user1['id'],
            'type' => 'returning the item',
            'notificationMessage' => 'Sample User is returning the item MRR1.',
            'isRead' => 0,
            'typeValueId' => $returns['id']
        ]);

        $regeneratedMessage = $this->notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

    }

    public function testRegenerateNotificationForApproveTheReturn() {
        $user1 = User::factory()->create([
            'firstName' => 'Sample User'
        ]);
        $user2 = User::factory()->create([
            'firstName' => 'Lester'
        ]);
        $this->actingAs($user1);
        
        $item = Item::factory()->create([
            'itemName' => 'Monitorr',
            'itemCode' => 'MRR1',
        ]);

        $requests = Requests::factory()->create([
            'idRequester' => $user1['id'],
            'idItem' => $item['id'],
        ]);

        $returns = Returns::factory()->create([
            'idRequest' => $requests['id'],
            'idReturner' => $requests['idRequester'],
            'isApprove' => true
        ]);

        $notification = Notification::factory()->create([
            'recipientUserId' => $user1['id'],
            'senderUserId' => $user2['id'],
            'type' => 'approve the return',
            'notificationMessage' => 'Lester approve the return MRR1.',
            'isRead' => 0,
            'typeValueId' => $returns['id']
        ]);

        $regeneratedMessage = $this->notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

    }

    public function testRegenerateNotificationForSentMessage() {
        $user1 = User::factory()->create([
            'firstName' => 'Sample User'
        ]);
        $user2 = User::factory()->create([
            'firstName' => 'Lester'
        ]);
        $this->actingAs($user1);
        
        $item = Item::factory()->create([
            'itemName' => 'Monitorr',
            'itemCode' => 'MRR1',
        ]);

        $requests = Requests::factory()->create([
            'idRequester' => $user1['id'],
            'idItem' => $item['id'],
        ]);

        $notification = Notification::factory()->create([
            'recipientUserId' => $user2['id'],
            'senderUserId' => $user1['id'],
            'type' => 'sent a message',
            'notificationMessage' => 'Sample User sent a message on the request item with Reference #00'.$requests['id'].'.',
            'isRead' => 0,
            'typeValueId' => $requests['id']
        ]);

        $regeneratedMessage = $this->notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

    }

}
