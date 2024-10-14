<?php

namespace Tests\Unit;
use App\Models\User;
use App\Models\Item;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
class ItemTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */
    public function testCreateItem(): void
    {
        $user = User::factory()->create([
            'isAdmin' => true
        ]);
        $this->actingAs($user);
        $itemController = new \App\Http\Controllers\Api\ItemController;
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemName' => 'Sample Item',
            'itemCode' => 'AAA1',
        ]);
        $itemController->store($request);
        $this->assertDatabaseHas('items',[
            'itemName' => 'Sample Item',
            'itemCode' => 'AAA1'
        ]);

    }

    public function testCreateItemEmptyItemNameField(): void
    {
        $user = User::factory()->create([
            'isAdmin' => true
        ]);
        $this->actingAs($user);
        $itemController = new \App\Http\Controllers\Api\ItemController;
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemName' => '',
            'itemCode' => 'AAA1',
        ]);
        $response = $itemController->store($request);
        $getData = $response->getData();
        $this->assertTrue($getData->errors->itemName[0] === 'The item name field is required.');
        $this->assertDatabaseMissing('items', [
            'itemName' => '',
            'itemCode' => 'AAA1',
        ]);

    }

    public function testCreateItemEmptyItemCodeField(): void
    {
        $user = User::factory()->create([
            'isAdmin' => true
        ]);
        $this->actingAs($user);
        $itemController = new \App\Http\Controllers\Api\ItemController;
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemName' => 'Sample Item',
            'itemCode' => '',
        ]);
        $response = $itemController->store($request);
        $getData = $response->getData();
        $this->assertTrue($getData->errors->itemCode[0] === 'The item code field is required.');
        $this->assertDatabaseMissing('items', [
            'itemName' => 'Sample Item',
            'itemCode' => '',
        ]);

    }

    public function testCreateItemEmptyAllRequiredField(): void
    {
        $user = User::factory()->create([
            'isAdmin' => true
        ]);
        $this->actingAs($user);
        $itemController = new \App\Http\Controllers\Api\ItemController;
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemName' => '',
            'itemCode' => '',
        ]);
        $response = $itemController->store($request);
        $getData = $response->getData();
        $this->assertTrue($getData->errors->itemCode[0] === 'The item code field is required.');
        $this->assertTrue($getData->errors->itemName[0] === 'The item name field is required.');
        $this->assertDatabaseMissing('items', [
            'itemName' => '',
            'itemCode' => '',
        ]);

    }

    //----
    public function testCreateItemMissingItemNameField(): void
    {
        $user = User::factory()->create([
            'isAdmin' => true
        ]);
        $this->actingAs($user);
        $itemController = new \App\Http\Controllers\Api\ItemController;
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemCode' => 'AAA1',
        ]);
        $response = $itemController->store($request);
        $getData = $response->getData();
        $this->assertTrue($getData->errors->itemName[0] === 'The item name field is required.');
        $this->assertDatabaseMissing('items', [
            'itemName' => '',
            'itemCode' => 'AAA1',
        ]);

    }

    public function testCreateItemMissingItemCodeField(): void
    {
        $user = User::factory()->create([
            'isAdmin' => true
        ]);
        $this->actingAs($user);
        $itemController = new \App\Http\Controllers\Api\ItemController;
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemName' => 'Sample Item',
        ]);
        $response = $itemController->store($request);
        $getData = $response->getData();
        $this->assertTrue($getData->errors->itemCode[0] === 'The item code field is required.');
        $this->assertDatabaseMissing('items', [
            'itemName' => 'Sample Item',
            'itemCode' => '',
        ]);

    }

    public function testCreateItemMissingAllRequiredField(): void
    {
        $user = User::factory()->create([
            'isAdmin' => true
        ]);
        $this->actingAs($user);
        $itemController = new \App\Http\Controllers\Api\ItemController;
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
        ]);
        $response = $itemController->store($request);
        $getData = $response->getData();
        $this->assertTrue($getData->errors->itemCode[0] === 'The item code field is required.');
        $this->assertTrue($getData->errors->itemName[0] === 'The item name field is required.');
        $this->assertDatabaseMissing('items', [
            'itemName' => '',
            'itemCode' => '',
        ]);

    }
}
