<?php

namespace Tests\Unit;
use App\Models\User;
use App\Models\Item;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Api\ItemController;
class ItemTest extends TestCase
{
    use RefreshDatabase;
    protected $itemController;
    
    protected function setUp(): void {
        parent::setUp();

        $this->itemController = new ItemController();

    }
    public function testCreateItem(): void
    {
        $user = User::factory()->create([
            'isAdmin' => true
        ]);
        $this->actingAs($user);
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemName' => 'Sample Item',
            'itemCode' => 'AAA1',
        ]);
        $this->itemController->store($request);
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
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemName' => '',
            'itemCode' => 'AAA1',
        ]);
        $response = $this->itemController->store($request);
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
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemName' => 'Sample Item',
            'itemCode' => '',
        ]);
        $response = $this->itemController->store($request);
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
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemName' => '',
            'itemCode' => '',
        ]);
        $response = $this->itemController->store($request);
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
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemCode' => 'AAA1',
        ]);
        $response = $this->itemController->store($request);
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
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
            'itemName' => 'Sample Item',
        ]);
        $response = $this->itemController->store($request);
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
        // Create a new Request object with valid data
        $request = Request::create('/api/items', 'POST', [
        ]);
        $response = $this->itemController->store($request);
        $getData = $response->getData();
        $this->assertTrue($getData->errors->itemCode[0] === 'The item code field is required.');
        $this->assertTrue($getData->errors->itemName[0] === 'The item name field is required.');
        $this->assertDatabaseMissing('items', [
            'itemName' => '',
            'itemCode' => '',
        ]);

    }
}
