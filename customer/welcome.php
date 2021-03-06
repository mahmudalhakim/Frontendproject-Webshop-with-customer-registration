<?php
require_once '../second_header_extern.php';
require_once '../config/db.php';

$customersOrders = "";

//Hämta orderuppgifter från databasen utifrån inloggad e-postadress
// (hämtar både pågående + slutförda ordrar, dvs från två tabeller)
$sql = "SELECT * FROM webshop_orders WHERE email=:email 
        UNION ALL 
        SELECT * FROM webshop_orderscomplete WHERE email=:email2";
$stmt = $db->prepare($sql);
$stmt->bindParam(':email', $_SESSION["email"]);
$stmt->bindParam(':email2', $_SESSION["email"]);
$stmt->execute();

//Om kunden har några beställningar i databasen 
if ($stmt->rowCount() > 0) {

  //Skapa tabellhuvud med rubriker
  $orderedProducts;
  $customersOrders = "<table class='table_orders table_orders-container'>
            <tbody>
               <tr class='table_orders-row table_orders-head-row'>
                  <th class='table_orders-head-text'>Orderid</th>
                  <th class='table_orders-head-text'>Orderdatum</th>
                  <th class='table_orders-head-text'>Kunduppgifter</th>
                  <th class='table_orders-head-text'>Produkter</th>
                  <th class='table_orders-head-text'>Summa</th>
                  <th class='table_orders-head-text'>Status</th>
               </tr>";


  //För varje orderrad i databasen - hämta all orderinfo och spar ner i variabler
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $orderId = htmlspecialchars($row['orderid']);
    $orderDate = htmlspecialchars($row['orderdate']);
    $orderDate = substr($orderDate, 0, 10); //Hämtar de 10 första tecknen = bara datumet, utan tidsangivelsen)
    $orderStatusId = htmlspecialchars($row['status']);
    $totalPrice = htmlspecialchars($row['totalprice']);
    $name = htmlspecialchars($row['name']);
    $email = htmlspecialchars($row['email']);
    $phone = htmlspecialchars($row['phone']);
    $street = htmlspecialchars($row['street']);
    $zip = htmlspecialchars($row['zip']);
    $city = htmlspecialchars($row['city']);
    $products = json_decode($row['products'], true);
    $freight = htmlspecialchars($row['freight']);

    $zipcode = substr_replace($zip, " ", 3, 0);


    //Hämta värden från produktarrayen för att kunna skriva ut dem i orderbekräftelsen
    $orderedProducts = "";

    foreach ($products as $key => $value) {
      $pOutlet = "";
      $pPrice = "";
      foreach ($value as $ky => $val) {
        if ($ky == "cartQty") {
          $orderedProducts .= $val . " st ";
        }
        if ($ky == "title") {
          $orderedProducts .= $val;
        }
        if ($ky == "outletprice") {
          $pOutlet = $val;
        }
        if ($ky == "price") {
          $pPrice = $val;
          if ($pOutlet != null) {
            $orderedProducts .= " pris " . $pOutlet . " kr (ord pris " . $pPrice . " kr)";
          } else {
            $orderedProducts .= " pris " . $pPrice . " kr";
          }
        }
      }
      $orderedProducts .= "<br>";
    }

    //Översätter statuskoderna till text
    if ($orderStatusId == 1) {
      $orderStatus = "Mottagen";
    } elseif ($orderStatusId == 2) {
      $orderStatus = "Behandlas";
    } elseif ($orderStatusId == 3) {
      $orderStatus = "Slutförd";
    };

    //Skapa en tabell med orderdetaljerna som hämtats från databasen
    $customersOrders .= "
        <tr class='table_orders-row'>
            <td class='table_orders-cell conf-cell'> $orderId</td>
            <td class='table_orders-cell conf-cell'> $orderDate</td>
            <td class='table_orders-cell conf-cell'>
                $name <br> 
                <span class='email-style'>$email </span><br> 
                $phone <br> 
                $street, $zipcode $city
            </td>
            <td class='table_orders-cell conf-cell products'> $orderedProducts </td>
            <td class='table_orders-cell conf-cell'> $totalPrice kr <br>
                                                    varav frakt: $freight kr</td>
            <td class='table_orders-cell conf-cell'> $orderStatus</td>
        </tr>";
  }
  $customersOrders .= "</tbody></table>";

  //Om kunden inte har gjort några beställningar än
} else {
  $customersOrders = "<p>Du har inte lagt några beställningar än. Vill du börja handla nu?</p>";
}
?>


<section class="welcome-section">

  <h1>Välkommen <?php echo $_SESSION["name"] ?>!</h1>
  <br>

  <h2>Dina beställningar</h2><br>
  <div class='table_container'>
    <!-- här skrivs tabellen ut med kundens orderuppgifter -->
    <?php echo $customersOrders ?>
  </div>
  <br>


  <a href='../index.php'>
    <button type='button' class='form-container__submit-button'>Shoppa spel</button><br><br><br><br>
  </a>
  <a href='reset-password.php'>
    <button type='button' class='form-container__submit-button'>Återställ lösenord</button>
  </a>

</section>


<?php require_once '../footer.php' ?>