# pepipost
Pepipost API Wrapper for Laravel

# How to Use
Add the Service Provider and Alias to your app config
    
    Xwiz\Pepipost\PepipostServiceProvider
    
    'Pepipost' => Xwiz\Pepipost\Facades\Pepipost

Use as you would use regular Mail facade. E.g.:

    Pepipost::send('email.template', $data, function()
    {
        //etc
    });
