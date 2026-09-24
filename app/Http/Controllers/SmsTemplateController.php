<?php

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:sms_templates'])->only(['index', 'edit', 'update']);
    }

    public function index()
    {
        $smsTemplates = SmsTemplate::orderBy('identifier')->paginate(15);
        return view('backend.sms_templates.index', compact('smsTemplates'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $smsTemplate = SmsTemplate::findOrFail($id);
        return view('backend.sms_templates.edit', compact('smsTemplate'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'sms_body' => 'required|string|max:1600',
        ]);

        $smsTemplate = SmsTemplate::findOrFail($id);
        $smsTemplate->sms_body = $request->sms_body;
        $smsTemplate->status = $request->has('status') ? 1 : 0;
        $smsTemplate->save();

        flash(translate('SMS Template has been updated successfully'))->success();
        return back();
    }

    public function destroy($id)
    {
        //
    }
}
