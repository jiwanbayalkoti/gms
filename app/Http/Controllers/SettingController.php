<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:GymAdmin']);
    }
    
    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $settings = Setting::current();
        
        return view('settings.index', compact('settings'));
    }
    
    /**
     * Update the settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'gym_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'footer_text' => 'nullable|string|max:255',
            'enable_online_booking' => 'boolean',
            'enable_online_payments' => 'boolean',
            'enable_sms_notifications' => 'boolean',
            'sms_provider' => 'nullable|string|in:twilio,textlocal,sparrow',
            'textlocal_api_key' => 'nullable|string|max:255',
            'textlocal_sender_id' => 'nullable|string|max:20',
            'twilio_account_sid' => 'nullable|string|max:255',
            'twilio_auth_token' => 'nullable|string|max:255',
            'twilio_from_number' => 'nullable|string|max:20',
            'sparrow_sms_token' => 'nullable|string|max:255',
            'sparrow_sms_from' => 'nullable|string|max:11',
            'enable_email_notifications' => 'boolean',
            'enable_pause_feature' => 'boolean',
            'minimum_pause_days' => 'required|integer|min:1',
        ]);
        
        $settings = Setting::current();
        
        // Handle checkbox fields that may not be present in the request
        $settings->enable_online_booking = $request->has('enable_online_booking');
        $settings->enable_online_payments = $request->has('enable_online_payments');
        $settings->enable_sms_notifications = $request->has('enable_sms_notifications');
        $settings->sms_provider = $request->input('sms_provider', 'twilio');
        $settings->textlocal_api_key = $request->input('textlocal_api_key');
        $settings->textlocal_sender_id = $request->input('textlocal_sender_id');
        $settings->twilio_account_sid = $request->input('twilio_account_sid');
        $settings->twilio_auth_token = $request->input('twilio_auth_token');
        $settings->twilio_from_number = $request->input('twilio_from_number');
        $settings->sparrow_sms_token = $request->input('sparrow_sms_token');
        $settings->sparrow_sms_from = $request->input('sparrow_sms_from');
        $settings->enable_email_notifications = $request->has('enable_email_notifications');
        $settings->enable_pause_feature = $request->has('enable_pause_feature');
        $settings->minimum_pause_days = $request->input('minimum_pause_days', 7);
        
        // Handle file upload for logo
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo && Storage::exists('public/' . $settings->logo)) {
                Storage::delete('public/' . $settings->logo);
            }
            
            $logoPath = $request->file('logo')->store('logos', 'public');
            $settings->logo = $logoPath;
        }
        
        // Update other fields
        $settings->gym_name = $request->gym_name;
        $settings->email = $request->email;
        $settings->phone = $request->phone;
        $settings->address = $request->address;
        $settings->primary_color = $request->primary_color;
        $settings->secondary_color = $request->secondary_color;
        $settings->footer_text = $request->footer_text;
        
        $settings->save();
        
        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $settings = Setting::current();
        return $this->apiSuccess($settings, 'Settings retrieved successfully');
    }

    public function apiUpdate(Request $request)
    {
        $request->validate([
            'gym_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'footer_text' => 'nullable|string|max:255',
            'enable_online_booking' => 'boolean',
            'enable_online_payments' => 'boolean',
            'enable_sms_notifications' => 'boolean',
            'sms_provider' => 'nullable|string|in:twilio,textlocal,sparrow',
            'textlocal_api_key' => 'nullable|string|max:255',
            'textlocal_sender_id' => 'nullable|string|max:20',
            'twilio_account_sid' => 'nullable|string|max:255',
            'twilio_auth_token' => 'nullable|string|max:255',
            'twilio_from_number' => 'nullable|string|max:20',
            'sparrow_sms_token' => 'nullable|string|max:255',
            'sparrow_sms_from' => 'nullable|string|max:11',
            'enable_email_notifications' => 'boolean',
            'enable_pause_feature' => 'boolean',
            'minimum_pause_days' => 'required|integer|min:1',
        ]);
        
        $settings = Setting::current();
        
        $settings->enable_online_booking = $request->has('enable_online_booking');
        $settings->enable_online_payments = $request->has('enable_online_payments');
        $settings->enable_sms_notifications = $request->has('enable_sms_notifications');
        $settings->sms_provider = $request->input('sms_provider', 'twilio');
        $settings->textlocal_api_key = $request->input('textlocal_api_key');
        $settings->textlocal_sender_id = $request->input('textlocal_sender_id');
        $settings->twilio_account_sid = $request->input('twilio_account_sid');
        $settings->twilio_auth_token = $request->input('twilio_auth_token');
        $settings->twilio_from_number = $request->input('twilio_from_number');
        $settings->sparrow_sms_token = $request->input('sparrow_sms_token');
        $settings->sparrow_sms_from = $request->input('sparrow_sms_from');
        $settings->enable_email_notifications = $request->has('enable_email_notifications');
        $settings->enable_pause_feature = $request->has('enable_pause_feature');
        $settings->minimum_pause_days = $request->input('minimum_pause_days', 7);
        
        if ($request->hasFile('logo')) {
            if ($settings->logo && Storage::exists('public/' . $settings->logo)) {
                Storage::delete('public/' . $settings->logo);
            }
            $logoPath = $request->file('logo')->store('logos', 'public');
            $settings->logo = $logoPath;
        }
        
        $settings->gym_name = $request->gym_name;
        $settings->email = $request->email;
        $settings->phone = $request->phone;
        $settings->address = $request->address;
        $settings->primary_color = $request->primary_color;
        $settings->secondary_color = $request->secondary_color;
        $settings->footer_text = $request->footer_text;
        
        $settings->save();
        
        return $this->apiSuccess($settings, 'Settings updated successfully');
    }
}
