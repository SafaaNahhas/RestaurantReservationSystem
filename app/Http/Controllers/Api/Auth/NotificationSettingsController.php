<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationSettings;
use App\Http\Requests\NotificationSettings\StoreNotificationSettings;

class NotificationSettingsController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function checkIfNotificationSettingsExsits()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNotificationSettings $request)
    {

    }

        /**
     * Store a newly created resource in storage.
     */
    public function update(Request $request)
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     */
    public function resetNotificationSettings()
    {
        //
    }
}
