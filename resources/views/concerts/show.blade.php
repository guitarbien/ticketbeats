<h1>{{ $concert->title }}</h1>
<h2>{{ $concert->subtitle }}</h2>
<p>{{ $concert->formatted_date }}</p>
<p>Doors at {{ $concert->formatted_start_time }}</p>
<p>{{ number_format($concert->ticket_price / 100, 2) }}</p>
<h2>{{ $concert->venue }}</h2>
<h2>{{ $concert->venue_address }}</h2>
<h2>{{ $concert->city }}, {{ $concert->state }} {{ $concert->zip }}</h2>
<h2>{{ $concert->additional_information }}</h2>