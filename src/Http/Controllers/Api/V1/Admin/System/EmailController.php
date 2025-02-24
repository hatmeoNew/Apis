<?php
namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\System;

use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\AdminController;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\ShipmentRepository;
use Illuminate\Support\Facades\Mail;
use Webkul\Shop\Mail\Order\CreatedNotification;
use Webkul\Shop\Mail\Order\CanceledNotification;
use Webkul\Sales\Contracts\OrderComment;
use Webkul\Shop\Mail\Order\ShipmentNotification;
use Illuminate\Support\Facades\Log;


class EmailController extends AdminController
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected ShipmentRepository $shipmentRepository
    )
    {
        parent::__construct();
    }
    
    // manual send order email
    public function sendOrderEmail($order_id, $email_type)
    {
        $order = $this->orderRepository->find($order_id);
        if (!$order) {
            return $this->responseError('Order not found');
        }

        if ($email_type == 'created') {
            $this->prepareMail($order, new CreatedNotification($order));
        } else if ($email_type == 'canceled') {
            $this->prepareMail($order, new CanceledNotification($order));
        }else {
            return $this->responseError('Email type not found');
        }

        return response()->json([
            'message' => 'Email sent successfully',
            'email_type' => $email_type,
            'order_id' => $order_id,
            'email' => $order->customer_email
        ]); 

    }

    public function sendShippingEmail($shipping_id)
    {
        $shipment = $this->shipmentRepository->find($shipping_id);
        if (!$shipment) {
            return $this->responseError('Shipment not found');
        }

        if ($shipment->email_sent) {
            return $this->responseError('Email already sent');
        }

        try {
            if (! core()->getConfigData('emails.general.notifications.emails.general.notifications.new_shipment')) {
                return $this->responseError('Email configuration not found');
            }

            $this->prepareMail($shipment, new ShipmentNotification($shipment));
        } catch (\Exception $e) {
            report($e);
            return $this->responseError('Email sending failed');
        }

        return response()->json([
            'message' => 'Email sent successfully',
            'email_type' => 'shipment',
            'shipment_id' => $shipping_id,
            'email' => $shipment->order->customer_email
        ]); 
    }

    protected function getLocale($object)
    {
        if ($object instanceof OrderComment) {
            $object = $object->order;
        }

        $objectFirstItem = $object->items->first();

        return $objectFirstItem->additional['locale'] ?? config('app.locale');
    }

    /**
     * Prepare mail.
     *
     * @return void
     */
    protected function prepareMail($entity, $notification)
    {
        $customerLocale = $this->getLocale($entity);

        $previousLocale = core()->getCurrentLocale()->code;

        app()->setLocale($customerLocale);

        try {

            // Log the notification data
            Log::info('Queueing email notification: ', ['notification' => $notification]);

            Mail::queue($notification);
        } catch(\Exception $e) {
            var_dump($e->getMessage());
            report($e);
        }

        app()->setLocale($previousLocale);
    }
}