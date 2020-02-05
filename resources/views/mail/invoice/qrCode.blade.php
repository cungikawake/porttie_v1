<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
    
<div class="visible-print text-center">
	<h1>Laravel 5.7 - QR Code Generator  </h1>
     
    {!! QrCode::size(250)->generate('http://localhost:8000/account/orders'); !!}
     
    <p>example by ItSolutionStuf.com.</p>
</div>
    
</body>
</html>