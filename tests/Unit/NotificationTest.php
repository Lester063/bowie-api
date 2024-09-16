<?php

namespace Tests\Unit;
namespace App\Http\Controllers\api;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use App\Models\User;
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
            'recipientUserId' => $user->id,
            'senderUserId' => '2',
            'type' => 'ehehe',
            'notificationMessage' => 'Lester is requesting the item OGE.',
            'isRead' => 0,
            'typeValueId' => '123'
        ]);
        $this->assertTrue($notification['isRead'] == false);

        $notificationController->readUnreadUserNotification();

        $this->assertDatabaseHas('notifications', [
            'recipientUserId' => $user->id,
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
            'recipientUserId' => $user->id,
            'senderUserId' => '2',
            'type' => 'ehehe',
            'notificationMessage' => 'Lester is requesting the item OGE.',
            'isRead' => 0,
            'typeValueId' => '123'
        ]);

        $regeneratedMessage = $notificationController->regenerateNotificationMessage($notification['id']);
        $this->assertTrue($regeneratedMessage == 'Invalid');

        //clean up
        $user->delete();
        $notification->delete();
    }

}
