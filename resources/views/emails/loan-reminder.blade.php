<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $isOverdue ? 'Overdue Payment Notice' : 'Payment Reminder' }}</title>
  <!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; background-color: #f0f4f8; color: #1a2b3c; }
    .wrapper { max-width: 600px; margin: 0 auto; padding: 24px 16px; }

    .header { background: linear-gradient(135deg, #0D1B2A 0%, #16293D 100%); border-radius: 12px 12px 0 0; padding: 28px 32px; text-align: center; }
    .header-overdue { background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); }
    .logo-text { font-size: 22px; font-weight: 700; color: #F0F6FF; letter-spacing: 0.04em; }
    .logo-sub  { font-size: 11px; color: #0B8FAC; letter-spacing: 0.12em; text-transform: uppercase; margin-top: 4px; }
    .badge { display: inline-block; margin-top: 14px; padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
    .badge-warning { background: rgba(245,166,35,0.2); color: #F5A623; border: 1px solid rgba(245,166,35,0.4); }
    .badge-danger  { background: rgba(239,68,68,0.2);  color: #FCA5A5; border: 1px solid rgba(239,68,68,0.4); }
    .badge-info    { background: rgba(11,143,172,0.2); color: #13AECF; border: 1px solid rgba(11,143,172,0.4); }

    .body-card { background: #ffffff; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; padding: 32px; }

    .greeting { font-size: 18px; font-weight: 600; color: #0D1B2A; margin-bottom: 12px; }
    .intro    { font-size: 14px; line-height: 1.6; color: #4a5568; margin-bottom: 24px; }

    .amount-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px 24px; margin-bottom: 24px; }
    .amount-box-overdue { background: #fff5f5; border-color: #feb2b2; }
    .amount-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px; }
    .amount-value { font-size: 30px; font-weight: 800; color: #0D1B2A; font-family: 'Courier New', monospace; }
    .amount-value-overdue { color: #c53030; }
    .amount-sub { font-size: 13px; color: #64748b; margin-top: 6px; }

    .details-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    .details-table td { padding: 10px 12px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
    .details-table td:first-child { color: #64748b; width: 48%; }
    .details-table td:last-child  { font-weight: 600; color: #1a2b3c; text-align: right; }
    .details-table tr:last-child td { border-bottom: none; }

    .cta-box { text-align: center; padding: 20px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; margin-bottom: 24px; }
    .cta-box-overdue { background: #fff5f5; border-color: #fecaca; }
    .cta-text { font-size: 13px; color: #166534; font-weight: 500; margin-bottom: 4px; }
    .cta-text-overdue { color: #991b1b; }
    .cta-phone { font-size: 20px; font-weight: 800; color: #0D1B2A; letter-spacing: 0.04em; }

    .officer-box { background: #f8fafc; border-radius: 8px; padding: 16px 20px; margin-bottom: 24px; display: flex; gap: 12px; align-items: flex-start; }
    .officer-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: 700; margin-bottom: 3px; }
    .officer-name  { font-size: 14px; font-weight: 600; color: #1a2b3c; }
    .officer-phone { font-size: 13px; color: #0B8FAC; }

    .warning-box { background: #fffbeb; border-left: 3px solid #F5A623; padding: 14px 18px; border-radius: 0 8px 8px 0; margin-bottom: 24px; font-size: 13px; color: #744210; line-height: 1.5; }
    .danger-box  { background: #fff5f5;  border-left: 3px solid #EF4444; padding: 14px 18px; border-radius: 0 8px 8px 0; margin-bottom: 24px; font-size: 13px; color: #7f1d1d; line-height: 1.5; }

    .divider { border: none; border-top: 1px solid #e2e8f0; margin: 20px 0; }

    .footer { background: #f8fafc; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 12px 12px; padding: 20px 32px; text-align: center; }
    .footer-name { font-size: 13px; font-weight: 600; color: #1a2b3c; }
    .footer-meta { font-size: 11px; color: #94a3b8; margin-top: 4px; line-height: 1.6; }
    .footer-disclaimer { font-size: 11px; color: #b0bec5; margin-top: 12px; line-height: 1.5; border-top: 1px solid #e2e8f0; padding-top: 12px; }
  </style>
</head>
<body>
<div class="wrapper">

  {{-- HEADER --}}
  <div class="header {{ $isOverdue ? 'header-overdue' : '' }}">
    <div class="logo-text">{{ $company['name'] }}</div>
    <div class="logo-sub">Loan Management System</div>
    @if($isOverdue)
      <div class="badge badge-danger">⚠ Overdue Notice</div>
    @elseif($isDueToday)
      <div class="badge badge-warning">Due Today</div>
    @else
      <div class="badge badge-info">Payment Reminder</div>
    @endif
  </div>

  {{-- BODY --}}
  <div class="body-card">

    <div class="greeting">Dear {{ $context['first_name'] }} {{ $context['last_name'] }},</div>

    @if($isOverdue)
      <div class="intro">
        Your loan repayment for <strong>Loan {{ $context['loan_number'] }}</strong>,
        Instalment {{ $context['instalment_no'] }}, is now
        <strong>{{ $context['days_overdue'] }} day(s) overdue</strong>.
        Please make arrangements to clear this balance as soon as possible to avoid further penalties.
      </div>
    @elseif($isDueToday)
      <div class="intro">
        This is a reminder that your loan repayment for <strong>Loan {{ $context['loan_number'] }}</strong>,
        Instalment {{ $context['instalment_no'] }}, is <strong>due today</strong>.
        Please ensure payment is made today to avoid any late charges.
      </div>
    @else
      <div class="intro">
        This is a friendly reminder that your loan repayment for <strong>Loan {{ $context['loan_number'] }}</strong>,
        Instalment {{ $context['instalment_no'] }}, is due on
        <strong>{{ $context['due_date'] }}</strong>.
        Please ensure funds are available in your account on time.
      </div>
    @endif

    {{-- AMOUNT BOX --}}
    <div class="amount-box {{ $isOverdue ? 'amount-box-overdue' : '' }}">
      <div class="amount-label">{{ $isOverdue ? 'Overdue Amount' : 'Amount Due' }}</div>
      <div class="amount-value {{ $isOverdue ? 'amount-value-overdue' : '' }}">
        ZMW {{ $context['amount_due'] }}
      </div>
      <div class="amount-sub">Due Date: {{ $context['due_date'] }}</div>
    </div>

    {{-- DETAILS TABLE --}}
    <table class="details-table">
      <tr>
        <td>Loan Number</td>
        <td>{{ $context['loan_number'] }}</td>
      </tr>
      <tr>
        <td>Instalment</td>
        <td>{{ $context['instalment_no'] }}</td>
      </tr>
      <tr>
        <td>Total Outstanding Balance</td>
        <td>ZMW {{ $context['total_due'] }}</td>
      </tr>
      @if($isOverdue && $context['total_penalties'] > 0)
      <tr>
        <td>Outstanding Penalties</td>
        <td style="color:#c53030">ZMW {{ $context['total_penalties'] }}</td>
      </tr>
      @endif
    </table>

    {{-- OVERDUE WARNING --}}
    @if($isOverdue)
      <div class="danger-box">
        <strong>Important:</strong> Penalties of {{ $context['penalty_rate'] }}% per instalment continue to
        accumulate each day this account remains unpaid. To avoid further charges, please settle this
        balance immediately or contact us to discuss a payment plan.
      </div>
    @elseif($triggerKey === 'pre_due_3_days')
      <div class="warning-box">
        <strong>Action required in 3 days.</strong> Please ensure your account has sufficient funds
        or visit our office before {{ $context['due_date'] }} to avoid a late payment fee.
      </div>
    @endif

    {{-- CTA --}}
    <div class="cta-box {{ $isOverdue ? 'cta-box-overdue' : '' }}">
      <div class="cta-text {{ $isOverdue ? 'cta-text-overdue' : '' }}">
        {{ $isOverdue ? 'Contact us immediately to resolve this balance' : 'To make a payment or enquire, call us' }}
      </div>
      <div class="cta-phone">{{ $company['phone'] }}</div>
    </div>

    {{-- OFFICER --}}
    @if(!empty($context['officer_name']))
    <div class="officer-box">
      <div>
        <div class="officer-label">Your Loan Officer</div>
        <div class="officer-name">{{ $context['officer_name'] }}</div>
        @if(!empty($context['officer_phone']))
          <div class="officer-phone">{{ $context['officer_phone'] }}</div>
        @endif
      </div>
    </div>
    @endif

    <hr class="divider">
    <div style="font-size:12px; color:#94a3b8; line-height:1.6;">
      If you have already made this payment, please disregard this notice.
      You can verify your balance by contacting our office.
    </div>
  </div>

  {{-- FOOTER --}}
  <div class="footer">
    <div class="footer-name">{{ $company['name'] }}</div>
    <div class="footer-meta">
      {{ $company['address'] }}<br>
      {{ $company['email'] }} &bull; {{ $company['phone'] }}
      @if(!empty($company['boz']))
        <br>BoZ Licence: {{ $company['boz'] }}
      @endif
    </div>
    <div class="footer-disclaimer">
      This is an automated reminder from our Loan Management System.
      Please do not reply to this email — use the contact details above to reach us.
    </div>
  </div>

</div>
</body>
</html>
