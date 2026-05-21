Hi {{ $user->name }},

You left items in your {{ $siteName }} cart (total ₹{{ number_format($cartTotal, 2) }}).

Complete checkout: {{ $cartUrl }}
