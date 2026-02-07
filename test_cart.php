use App\Services\CartService;
use Illuminate\Support\Facades\Session;

$cartService = new CartService();
$itemId = '36c967b4-2399-42c7-996c-886ba0ade4d5';

echo "Adding 1 item...\n";
$cartService->add($itemId, 1);
print_r(Session::get('cart'));

echo "\nAdding 2 items...\n";
$cartService->add($itemId, 2);
print_r(Session::get('cart'));

echo "\nCount: " . $cartService->count() . "\n";

echo "Clearing cart...\n";
$cartService->clear();
print_r(Session::get('cart'));
