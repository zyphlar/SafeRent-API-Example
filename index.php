<?php
$debug = true;
$xmlTemplate = "request.xml";

echo "<h1>SafeRent API Example - ALPHA</h1>";
echo "<h3>Results can take 30 seconds to come back; please click once & be patient.</h3>";

if(isset($_GET['inc'])) {

  $xmlreq = new SimpleXmlElement(file_get_contents($xmlTemplate));       // load and parse local file as XML
  $mits = $xmlreq->Applicant->Customers->children('MITS',true);    // parse the customer MITS namespace

  // load user input here
  if(isset($_GET['inc']) && $_GET['inc'] == 3)
    $xmlreq->Applicant->Income->EmploymentGrossIncome = 3;
  if(isset($_GET['inc']) && $_GET['inc'] == 4)
    $xmlreq->Applicant->Income->EmploymentGrossIncome = 4;
  if(isset($_GET['inc']) && $_GET['inc'] == 5)
    $xmlreq->Applicant->Income->EmploymentGrossIncome = 5;

  $data = http_build_query(array('XMLPost' => $xmlreq->asXML()));   // encode XML into URL variables
  $rawxmlresp = do_post_request("https://vendors.residentscreening.net/b2b/demits.aspx", $data);    // send the POST to the server
  $xmlresp = new SimpleXmlElement($rawxmlresp);       // parse response as XML

  if($xmlresp->Response->ApplicationDecision == "ACCEPT") {
    echo $mits->Customer->Name->FirstName.", your application has been <strong>approved!</strong> Please contact the leasing office for further information.";
  }
  else {
    echo "Thank you for submitting your application, ".$mits->Customer->Name->FirstName."! Please contact the leasing office for further information.";
  }

  echo "<br/><em>".$xmlresp->Response->ApplicantDecision."</em><br/>";

}

?>

<a href="/screening/?inc=3">Test Approved</a><br/>
<a href="/screening/?inc=4">Test Conditional</a><br/>
<a href="/screening/?inc=5">Test Denied</a><br/>

<?php

if($debug && isset($_GET['inc'])){
  echo "<h2>Debug Information</h2>";
  echo "<p><em>";
  echo $xmlresp->Response->ApplicationDecision."<br/>";
  echo $xmlresp->Response->ApplicantDecision."<br/>";

  echo $xmlreq->Applicant->AS_Information->SocSecNumber."<br/>";
  echo $xmlreq->Applicant->AS_Information->Birthdate."<br/>";
  echo $xmlreq->Applicant->Income->EmploymentGrossIncome."<br/>";
  echo $xmlreq->Applicant->Other->CurrentRent."<br/>";

  echo $mits->Customer->Name->FirstName."<br/>";
  echo $mits->Customer->Name->LastName."<br/>";
  echo $mits->Customer->Address->Address1."<br/>";
  echo $mits->Customer->Address->City."<br/>";
  echo $mits->Customer->Address->State."<br/>";
  echo $mits->Customer->Address->PostalCode."<br/>";
  echo "</em></p>";
  echo '<pre>';
  echo $data;
  echo '----------------------';
  var_dump($xmlreq);
  echo '----------------------';
  var_dump($mits);
  echo '----------------------';
  var_dump($xmlresp);
  echo '<pre>';
}

/////////////// Functions
function do_post_request($url, $data, $optional_headers = null)
{
  $params = array('http' => array(
              'method' => 'POST',
              'content' => $data
            ));
  if ($optional_headers !== null) {
    $params['http']['header'] = $optional_headers;
  }
  $ctx = stream_context_create($params);
  $fp = @fopen($url, 'rb', false, $ctx);
  if (!$fp) {
    throw new Exception("Problem with $url, $php_errormsg");
  }
  $response = @stream_get_contents($fp);
  if ($response === false) {
    throw new Exception("Problem reading data from $url, $php_errormsg");
  }
  return $response;
}
