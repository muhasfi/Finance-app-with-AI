<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
| User hanya boleh listen ke channel miliknya sendiri.
| Format channel: private-user.{userId}
*/

Broadcast::channel('user.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
});
