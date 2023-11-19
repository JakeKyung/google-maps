<!DOCTYPE html>
<html lang="en">
<head>
   
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  
    <title>Google Maps Geocoding Example with PHP</title>
  
    <style>
    #gmap_canvas{
        width:100%;
        height:30em;
    }
    </style>
  
</head>
<body>

<form action="" method="post">
    <input type='text' name='address' placeholder='Enter any address here' />
    <input type='submit' value='Geocode!' />
</form>

<div id='address-examples'>
    <div>Address examples:</div>
    <div>1. 인천광역시 문학경기장</div>
    <div>2. 서울특별시 선릉역</div>
</div>

</body>
</html>



<?php 
// function to geocode address, it will return false if unable to geocode address
function geocode($address){

    // url encode the address
    $address = urlencode($address);
    
    // google map geocode api url : YOUR_API_KEY
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key=YOUR_API_KEY";

		
		// cURL 세션 초기화
		$curl = curl_init();

		// cURL 옵션 설정
		curl_setopt($curl, CURLOPT_URL, $url); // 요청할 URL 설정
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 결과를 문자열로 반환받음
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // SSL 인증서 검사 비활성화 (필요에 따라)

		// HTTP 요청 실행 및 응답 받기
		$resp_json = curl_exec($curl);

		// 오류 체크
		if (curl_errno($curl)) {
			echo 'Curl error: ' . curl_error($curl);
		}

		// cURL 세션 종료
		curl_close($curl);
    // get the json response
    //$resp_json = file_get_contents($url);
      
    // decode the json
    $resp = json_decode($resp_json, true);
  
    // response status will be 'OK', if able to geocode given address 
    if($resp['status']=='OK'){
  
        // get the important data
        $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
        $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";
        $formatted_address = isset($resp['results'][0]['formatted_address']) ? $resp['results'][0]['formatted_address'] : "";
          
        // verify if data is complete
        if($lati && $longi && $formatted_address){
          
            // put the data in the array
            $data_arr = array();            
              
            array_push(
                $data_arr, 
                    $lati, 
                    $longi, 
                    $formatted_address
                );
              
            return $data_arr;
              
        }else{
            return false;
        }
          
    }
  
    else{
        echo "<strong>ERROR: {$resp['status']}</strong>";
        return false;
    }
}
?>


<?php
if($_POST){
	// get latitude, longitude and formatted address
    $data_arr = geocode($_POST['address']);
  
    // if able to geocode the address
    if($data_arr){
        $latitude = $data_arr[0];
        $longitude = $data_arr[1];
        $formatted_address = $data_arr[2];
                      
    ?>
  
    <!-- google map will be shown here -->
    <div id="gmap_canvas">Loading map...</div>
    <div id='map-label'>Map shows approximate location.</div>
  
    <!-- JavaScript to show google map -->
    <script type="text/javascript" src="https://maps.google.com/maps/api/js?key=YOUR_API_KEY"></script>   
    <script type="text/javascript">
        function init_map() {
            var myOptions = {
                zoom: 14,
                center: new google.maps.LatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>),
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById("gmap_canvas"), myOptions);
            marker = new google.maps.Marker({
                map: map,
                position: new google.maps.LatLng(<?php echo $latitude; ?>, <?php echo $longitude; ?>)
            });
            infowindow = new google.maps.InfoWindow({
                content: "<?php echo $formatted_address; ?>"
            });
            google.maps.event.addListener(marker, "click", function () {
                infowindow.open(map, marker);
            });
            infowindow.open(map, marker);
        }
        google.maps.event.addDomListener(window, 'load', init_map);
    </script>
  
    <?php
  
    // if unable to geocode the address
    }else{
        echo "No map found.";
    }
}
?>