import { createClient } from '@supabase/supabase-js'

// Initialize Supabase client
const supabaseUrl = window.supabaseConfig.url;
const supabaseKey = window.supabaseConfig.key;
const supabase = createClient(supabaseUrl, supabaseKey);

// Function to subscribe to order updates
window.subscribeToOrder = function(orderCode) {
    console.log(`Subscribing to order: ${orderCode}`);

    const channel = supabase
        .channel(`order-${orderCode}`)
        .on(
            'postgres_changes',
            {
                event: 'UPDATE',
                schema: 'public',
                table: 'orders',
                filter: `code=eq.${orderCode}`
            },
            (payload) => {
                console.log('Order update received:', payload);
                if (payload.new && payload.new.status === 'paid') {
                    // Redirect to tracking page
                    window.location.href = `/tracking/${orderCode}`;
                }
            }
        )
        .subscribe((status) => {
            console.log(`Subscription status: ${status}`);
        });
};
