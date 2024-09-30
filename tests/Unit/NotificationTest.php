<?php

namespace Tests\Unit;
namespace App\Http\Controllers\api;
use App\Models\Notification;
use App\Models\Requests;
use App\Models\User;
use App\Models\Item;
use App\Models\Returns;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Mockery;

class NotificationTest extends TestCase
{
    //use RefreshDatabase;

    public function testSendNotification() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;
        $notification = $notificationController->sendNotification($data = [
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

        $notification->delete();
    }

    public function testGenerateMessageIsInvalid() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;
        $notificationMessage = $notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'invalid message',
            'requestID' => '1'
        ]);
        $this->assertTrue($notificationMessage == 'Invalid');
    }

    public function testGenerateMessageForRequestingTheItem() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;
        $notificationMessage = $notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'requesting the item',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester is requesting the item OGE.');
    }

    public function testGenerateMessageForReturningTheItem() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;
        $notificationMessage = $notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'returning the item',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester is returning the item OGE.');
    }

    public function testGenerateMessageForApproveTheRequest() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;
        $notificationMessage = $notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'approve the request',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester approve the request of item OGE.');
    }

    public function testGenerateMessageForCloseTheRequest() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;
        $notificationMessage = $notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'close the request',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester close the request of item OGE.');
    }

    public function testGenerateMessageForDeclineTheRequest() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;
        $notificationMessage = $notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'decline the request',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester decline the request of item OGE.');
    }

    public function testGenerateMessageForApproveTheReturn() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;
        $notificationMessage = $notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'approve the return',
            'itemCode' => 'OGE'
        ]);

        $this->assertTrue($notificationMessage == 'Lester approve the return of item OGE.');
    }

    public function testGenerateMessageForSentMessage() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;
        $notificationMessage = $notificationController->generateNotificationMessage([
            'firstName' => 'Lester',
            'type' => 'sent a message',
            'requestID' => '1'
        ]);

        $this->assertTrue($notificationMessage == 'Lester sent a message on the request item with Reference #001.');
    }

    public function testReadUnreadUserNotification() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

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

        $notificationController->readUnreadUserNotification();

        $this->assertDatabaseHas('notifications', [
            'recipientUserId' => $user['id'],
            'senderUserId' => '2',
            'type' => 'ehehe',
            'notificationMessage' => 'Lester is requesting the item OGE.',
            'isRead' => true,
            'typeValueId' => '123'
        ]);
        $user->delete();
        $notification->delete();
    }

    public function testRegenerateNotificationMessageIsInvalid() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

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

        $regeneratedMessage = $notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == 'Invalid');

        //clean up
        $user->delete();
        $notification->delete();
    }

    public function testRegenerateNotificationForRequestingTheItem() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

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

        $regeneratedMessage = $notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

        $user1->delete();
        $user2->delete();
        $notification->delete();
        $requests->delete();
        $item->delete();
    }

    public function testRegenerateNotificationForApproveTheRequest() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

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

        $regeneratedMessage = $notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

        $user1->delete();
        $user2->delete();
        $notification->delete();
        $requests->delete();
        $item->delete();
    }

    public function testRegenerateNotificationForCloseTheRequest() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

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

        $regeneratedMessage = $notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

        $user1->delete();
        $user2->delete();
        $notification->delete();
        $requests->delete();
        $item->delete();
    }

    public function testRegenerateNotificationForDeclineTheRequest() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

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

        $regeneratedMessage = $notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

        $user1->delete();
        $user2->delete();
        $notification->delete();
        $requests->delete();
        $item->delete();
    }

    public function testRegenerateNotificationForReturningTheItem() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

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

        $regeneratedMessage = $notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

        $user1->delete();
        $user2->delete();
        $notification->delete();
        $requests->delete();
        $item->delete();
        $returns->delete();
    }

    public function testRegenerateNotificationForApproveTheReturn() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

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

        $regeneratedMessage = $notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

        $user1->delete();
        $user2->delete();
        $notification->delete();
        $requests->delete();
        $item->delete();
        $returns->delete();
    }

    public function testRegenerateNotificationForSentMessage() {
        $notificationController = new \App\Http\Controllers\Api\NotificationController;

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

        $regeneratedMessage = $notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == $notification['notificationMessage']);

        $user1->delete();
        $user2->delete();
        $notification->delete();
        $requests->delete();
        $item->delete();
    }

}
