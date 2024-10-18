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
    
    public function testShowItem(): void 
    {
        Item::factory()->create([
            'isDeleted' => false
        ]);
        $response = $this->itemController->index();
        $responseData = $response->getData();
        $this->assertEquals(1, count($responseData->data));
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
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->itemName[0] === 'The item name field is required.');
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
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->itemCode[0] === 'The item code field is required.');
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
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->itemCode[0] === 'The item code field is required.');
        $this->assertTrue($responseData->errors->itemName[0] === 'The item name field is required.');
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
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->itemName[0] === 'The item name field is required.');
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
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->itemCode[0] === 'The item code field is required.');
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
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->itemCode[0] === 'The item code field is required.');
        $this->assertTrue($responseData->errors->itemName[0] === 'The item name field is required.');
        $this->assertDatabaseMissing('items', [
            'itemName' => '',
            'itemCode' => '',
        ]);
    }

    public function testUpdateItemSuccessfully(): void
    {
        $item = Item::factory()->create([
            'itemName' => 'Keyboard',
            'itemCode' => 'K1'
        ]);
        //dd($item->id);
        $request = Request::create("/api/items/{$item->id}/edit", 'PUT', [
            'itemName' => 'New Item Name',
            'itemCode' => 'New K1'
        ]);
        $this->itemController->update($request, $item->id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'itemName' => 'New Item Name',
            'itemCode' => 'New K1'
        ]);
    }

    public function testUpdateItemMissingItemNameField(): void
    {
        $item = Item::factory()->create([
            'itemName' => 'Keyboard',
            'itemCode' => 'K1'
        ]);
        //dd($item->id);
        $request = Request::create("/api/items/{$item->id}/edit", 'PUT', [
            'itemName' => '',
            'itemCode' => 'New K1'
        ]);
        $response = $this->itemController->update($request, $item->id);
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->itemName[0] === 'The item name field is required.');
        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
            'itemName' => '',
            'itemCode' => 'New K1'
        ]);
    }

    public function testUpdateItemMissingItemCodeField(): void
    {
        $item = Item::factory()->create([
            'itemName' => 'Keyboard',
            'itemCode' => 'K1'
        ]);
        //dd($item->id);
        $request = Request::create("/api/items/{$item->id}/edit", 'PUT', [
            'itemName' => 'New Keyboard',
            'itemCode' => ''
        ]);
        $response = $this->itemController->update($request, $item->id);
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->itemCode[0] === 'The item code field is required.');
        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
            'itemName' => 'New Keyboard',
            'itemCode' => ''
        ]);
    }

    public function testUpdateItemMissingAllRequiredField(): void
    {
        $item = Item::factory()->create([
            'itemName' => 'Keyboard',
            'itemCode' => 'K1'
        ]);
        //dd($item->id);
        $request = Request::create("/api/items/{$item->id}/edit", 'PUT', [
            'itemName' => '',
            'itemCode' => ''
        ]);
        $response = $this->itemController->update($request, $item->id);
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->itemName[0] === 'The item name field is required.');
        $this->assertTrue($responseData->errors->itemCode[0] === 'The item code field is required.');
        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
            'itemName' => '',
            'itemCode' => ''
        ]);
    }

    public function testUpdateItemUnableToFindId(): void
    {
        $item = Item::factory()->create([
            'itemName' => 'Keyboard',
            'itemCode' => 'K1'
        ]);
        //dd($item->id);
        $request = Request::create("/api/items/{$item->id}/edit", 'PUT', [
            'itemName' => 'New Keyboard',
            'itemCode' => 'New K1'
        ]);
        $response = $this->itemController->update($request, 121212121);
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors === 'Unable to find the item.');
        $this->assertDatabaseMissing('items', [
            'id' => 121212121,
            'itemName' => '',
            'itemCode' => ''
        ]);
    }

    public function testUpdateItemItemCodeIsAlreadyTaken(): void
    {
        $item1 = Item::factory()->create([
            'itemName' => 'Keyboard',
            'itemCode' => 'K1'
        ]);
        $item2 = Item::factory()->create([
            'itemName' => 'Keyboard',
            'itemCode' => 'K2'
        ]);

        //dd($item->id);
        $request = Request::create("/api/items/{$item1->id}/edit", 'PUT', [
            'itemName' => 'New Keyboard',
            'itemCode' => $item2->itemCode
        ]);
        $response = $this->itemController->update($request, $item1->id);
        $responseData = $response->getData();
        $this->assertTrue($responseData->errors->itemCode === 'Item code is already taken.');
        $this->assertDatabaseMissing('items', [
            'id' => $item1->id,
            'itemName' => 'Keyboard',
            'itemCode' => $item2->itemCode
        ]);
    }
    
}
