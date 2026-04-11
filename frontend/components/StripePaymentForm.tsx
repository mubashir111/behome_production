'use client';

import React, { useState } from 'react';
import {
  PaymentElement,
  useStripe,
  useElements
} from '@stripe/react-stripe-js';
import { useToast } from './ToastProvider';

interface StripePaymentFormProps {
  orderId: number;
  onSuccess: () => void;
  onCancel: () => void;
}

export default function StripePaymentForm({ orderId, onSuccess, onCancel }: StripePaymentFormProps) {
  const stripe = useStripe();
  const elements = useElements();
  const { showToast } = useToast();
  const [isProcessing, setIsProcessing] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!stripe || !elements) {
      // Stripe.js has not yet loaded.
      // Make sure to disable form submission until Stripe.js has loaded.
      return;
    }

    setIsProcessing(true);
    setErrorMessage(null);

    const { error, paymentIntent } = await stripe.confirmPayment({
      elements,
      confirmParams: {
        // Make sure to change this to your payment completion page
        return_url: `${window.location.origin}/payment/success?order_id=${orderId}`,
      },
      redirect: 'if_required',
    });

    if (error) {
      if (error.type === "card_error" || error.type === "validation_error") {
        setErrorMessage(error.message || 'An error occurred.');
      } else {
        setErrorMessage("An unexpected error occurred.");
      }
      setIsProcessing(false);
    } else if (paymentIntent && paymentIntent.status === 'succeeded') {
      // Inline success (no 3DS redirect required). Navigate to the success page
      // WITH payment_intent in the URL so the verify endpoint is called exactly
      // as it would be after a Stripe redirect.
      window.location.href = `/payment/success?order_id=${orderId}&payment_intent=${paymentIntent.id}&redirect_status=succeeded`;
    } else {
      // Handle other statuses (e.g. processing, requires_action)
      // If redirect: 'if_required' and it requires action, stripe will handle it.
      // If it still doesn't finish, we might need a longer check.
      setIsProcessing(false);
    }
  };

  return (
    <form id="payment-form" onSubmit={handleSubmit} className="mt-4">
      <div className="bg-slate-800/40 p-4 rounded-xl border border-white/5 mb-4 shadow-inner">
        <PaymentElement
          id="payment-element"
          options={{
            layout: 'accordion',
          }}
        />
      </div>

      {errorMessage && (
        <div className="text-red fs-14 mb-4 px-3 py-2 bg-red/10 border-radius-6px border border-red/20 shadow-sm animate__animated animate__shakeX">
          <i className="feather icon-feather-alert-circle me-2"></i>
          {errorMessage}
        </div>
      )}

      <div className="d-flex flex-column flex-md-row gap-3">
        <button
          disabled={isProcessing || !stripe || !elements}
          id="submit"
          className="btn btn-base-color btn-extra-large btn-round-edge flex-grow-1 btn-box-shadow"
        >
          <span>
            <span className="btn-double-text" data-text={isProcessing ? "Processing..." : "Confirm Payment"}>
              {isProcessing ? "Processing..." : "Confirm Payment"}
            </span>
          </span>
        </button>
        <button
          type="button"
          onClick={onCancel}
          disabled={isProcessing}
          className="btn btn-transparent-white btn-extra-large btn-round-edge"
        >
          Cancel
        </button>
      </div>

      <p className="fs-12 text-white/40 text-center mt-20px">
        <i className="feather icon-feather-lock me-1"></i>
        Secure payment processed by Stripe
      </p>
    </form>
  );
}
