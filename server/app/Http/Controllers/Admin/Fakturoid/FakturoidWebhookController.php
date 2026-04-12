<?php

namespace App\Http\Controllers\Admin\Fakturoid;

use App\Http\Controllers\Controller;
use App\Services\FakturoidService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class FakturoidWebhookController extends Controller
{
	public function handle(Request $request): JsonResponse
	{
		$eventName = $request->get('event_name', '');
		$data = (object) $request->all();

		Log::info('Fakturoid webhook received: ' . $eventName, $request->all());

		try {
			$service = new FakturoidService();
			$service->handleWebhook($eventName, $data);
		} catch (\Throwable $e) {
			Log::error('Fakturoid webhook error: ' . $e->getMessage());
		}

		return Response::json(['status' => 'ok']);
	}
}
