<?php
$company_name = ConfigurationData::getByPreffix("company_name")->val;
// $symbol = ConfigurationData::getByPreffix("currency")->val;
// $iva_val = ConfigurationData::getByPreffix("imp-val")->val;
// $sell = SellData::getById($_GET["id"]);
// $stock = StockData::getById($sell->stock_to_id);
?>

<section class="content">
  <div class="btn-group pull-right">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
      <i class="fa fa-download"></i> Descargar <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
      <!-- <li><a href="report/onesell-word.php?id=<?php echo $_GET["id"]; ?>">Word (.docx)</a></li> -->
      <li><a onclick="thePDF()" id="makepdf" class=""><i class="fa fa-download"></i> Descargar PDF</a>
    </ul>
  </div>
  
  <h1>Resumen de Devolución</h1>
  <?php if (isset($_GET["id"]) && $_GET["id"] != "") : ?>
    <?php
    $sell = SellData::getById($_GET["id"]);
    $operations = OperationData::getAllProductsBySellId($_GET["id"]);
    $total = 0;
    ?>
    <?php
    if (isset($_COOKIE["selled"])) {
      foreach ($operations as $operation) {
        //    print_r($operation);
        $qx = OperationData::getQByStock($operation->product_id, StockData::getPrincipal()->id);
        // print "qx=$qx";
        $p = $operation->getProduct();
        if ($qx == 0) {
          echo "<p class='alert alert-danger'>El producto <b style='text-transform:uppercase;'> $p->name</b> no tiene existencias en inventario.</p>";
        } else if ($qx <= $p->inventary_min / 2) {
          echo "<p class='alert alert-danger'>El producto <b style='text-transform:uppercase;'> $p->name</b> tiene muy pocas existencias en inventario.</p>";
        } else if ($qx <= $p->inventary_min) {
          echo "<p class='alert alert-warning'>El producto <b style='text-transform:uppercase;'> $p->name</b> tiene pocas existencias en inventario.</p>";
        }
      }
      setcookie("selled", "", time() - 18600);
    }

    ?>
    <div class="box box-primary">
      <table class="table table-bordered">

        <?php if ($sell->person_id != "") :
          $client = $sell->getPerson();
        ?>
          <tr>
            <td style="width:150px;">Cliente</td>
            <td><?php echo $client->name . " " . $client->lastname; ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($sell->user_id != "") :
          $user = $sell->getUser();
        ?>
          <tr>
            <td>Atendido por</td>
            <td><?php echo $user->name . " " . $user->lastname; ?></td>
          </tr>
          <tr>
            <td>Devolución Factura #</td>
            <td><?php echo $sell->sell_from_id ?></td>
          </tr>
          <tr>
            <td>Devolución #</td>
            <td><?php echo $_GET["id"] ?></td>
          </tr>
        <?php endif; ?>
      </table>
    </div>
    <br>
    <div class="box box-primary">
      <table class="table table-bordered table-hover">
        <thead>
          <th>Codigo</th>
          <th>Cantidad</th>
          <th>Nombre del Producto</th>
          <th>Precio Unitario</th>
          <th>Total</th>

        </thead>
        <?php
        foreach ($operations as $operation) {
          $product  = $operation->getProduct();
        ?>
          <tr>
            <td><?php echo $product->id; ?></td>
            <td><?php echo $operation->q; ?></td>
            <td><?php echo $product->name; ?></td>
            <td><?php echo Core::$symbol; ?> <?php echo number_format($operation->price_out, 2, ".", ","); ?></td>
            <td><b><?php echo Core::$symbol; ?> <?php echo number_format($operation->q * $operation->price_out, 2, ".", ",");
                                                $total += $operation->q * $operation->price_out; ?></b></td>
          </tr>
        <?php
        }
        ?>
      </table>
    </div>
    <br><br>
    <div class="row">
      <div class="col-md-4">
        <div class="box box-primary">
          <table class="table table-bordered">
            <!-- <tr>
              <td>
                <h4>Descuento:</h4>
              </td>
              <td>
                <h4><?php echo Core::$symbol; ?> <?php echo number_format($sell->discount, 2, '.', ','); ?></h4>
              </td>
            </tr> -->
            <tr>
              <td>
                <h4>Subtotal:</h4>
                <?php
                $totalSImp = $total;
                ?>
              </td>
              <td>
                <h4><?php echo Core::$symbol; ?> <?php echo number_format($total, 2, '.', ','); ?></h4>
              </td>
            </tr>
            <tr>
              <td>
                <h4>IV:</h4>
                <?php
                $Imp = $totalSImp * 0.13;
                ?>
              </td>
              <td>
                <h4><?php echo Core::$symbol; ?> <?php echo number_format($Imp, 2, '.', ','); ?></h4>
              </td>
            </tr>
            <tr>
              <td>
                <h4>Total:</h4>
              </td>
              <td>
                <h4><?php echo Core::$symbol; ?> <?php echo number_format(($total + $Imp) -  $sell->discount, 2, '.', ','); ?></h4>
              </td>
            </tr>
          </table>
        </div>

        <!-- <?php if ($sell->person_id != "") :
                $credit = PaymentData::sumByClientId($sell->person_id)->total;

              ?>
          <div class="box box-primary">
            <table class="table table-bordered">
              <tr>
                <td>
                  <h4>Saldo anterior:</h4>
                </td>
                <td>
                  <h4><?php echo Core::$symbol; ?> <?php echo number_format($credit - $total, 2, '.', ','); ?></h4>
                </td>
              </tr>
              <tr>
                <td>
                  <h4>Saldo Actual:</h4>
                </td>
                <td>
                  <h4><?php echo Core::$symbol; ?> <?php echo number_format($credit, 2, '.', ','); ?></h4>
                </td>
              </tr>
            </table>
          </div>
        <?php endif; ?> -->
      </div>
    </div>

    <script type="text/javascript">
      function thePDF() {

        var columns = [
          //    {title: "Reten", dataKey: "reten"},
          {
            title: "Codigo",
            dataKey: "code"
          },
          {
            title: "Cantidad",
            dataKey: "q"
          },
          {
            title: "Nombre del Producto",
            dataKey: "product"
          },
          {
            title: "Precio unitario",
            dataKey: "pu"
          },
          {
            title: "Total",
            dataKey: "total"
          },
          //    ...
        ];


        var columns2 = [
          //    {title: "Reten", dataKey: "reten"},
          {
            title: "",
            dataKey: "clave"
          },
          {
            title: "",
            dataKey: "valor"
          },
          //    ...
        ];

        var rows = [
          <?php foreach ($operations as $operation) :
            $product  = $operation->getProduct();
          ?>

            {
              "code": "<?php echo $product->id; ?>",
              "q": "<?php echo $operation->q; ?>",
              "product": "<?php echo $product->name; ?>",
              "pu": "<?php echo Core::$symbol; ?> <?php echo number_format($operation->price_out, 2, ".", ","); ?>",
              "total": "<?php echo Core::$symbol; ?> <?php echo number_format($operation->q * $operation->price_out, 2, ".", ","); ?>",
            },
          <?php endforeach; ?>
        ];

        var rows2 = [
          <?php if ($sell->person_id != "") :
            $person = $sell->getPerson();
          ?>

            {
              "clave": "Cliente",
              "valor": "<?php echo $person->name . " " . $person->lastname; ?>",
            },
          <?php endif; ?> {
            "clave": "Atendido por",
            "valor": "<?php echo $user->name . " " . $user->lastname; ?>",
          },

        ];

        var rows3 = [

          {

            "clave": "Subtotal",
            "valor": "<?php
                      $totalSImp = $total;
                      echo Core::$symbol; ?> <?php echo number_format($totalSImp, 2, '.', ',');; ?>",
          },
          {
            "clave": "Impuesto",
            "valor": "<?php $Imp = $totalSImp * 0.13;
                      echo Core::$symbol; ?> <?php echo number_format($Imp, 2, '.', ',');; ?>",
          },
          {
            "clave": "Total",
            "valor": "<?php echo Core::$symbol; ?> <?php echo number_format(($total + $Imp) -  $sell->discount, 2, '.', ',');; ?>",
            //"valor": "<?php echo Core::$symbol; ?> <?php echo number_format(($total + $Imp) - $sell->discount, 2, '.', ',');; ?>",
          },
        ];

        var rows3 = [

          {

            "clave": "Subtotal",
            "valor": "<?php
                      $totalSImp = $total;
                      echo Core::$symbol; ?> <?php echo number_format($totalSImp, 2, '.', ',');; ?>",
          },
          {
            "clave": "Impuesto",
            "valor": "<?php $Imp = $totalSImp * 0.13;
                      echo Core::$symbol; ?> <?php echo number_format($Imp, 2, '.', ',');; ?>",
          },
          {
            "clave": "Total",
            "valor": "<?php echo Core::$symbol; ?> <?php echo number_format(($total + $Imp) -  $sell->discount, 2, '.', ',');; ?>",
            //"valor": "<?php echo Core::$symbol; ?> <?php echo number_format(($total + $Imp) - $sell->discount, 2, '.', ',');; ?>",
          },
        ];


        // Only pt supported (not mm or in)
        var doc = new jsPDF('p', 'pt');
        doc.setFontSize(24);
        doc.text("<?php echo $company_name; ?>", 40, 35);

        doc.setFontSize(11);
        doc.text("DEVOLUCIÓN: #<?php echo $sell->id; ?>", 420, 35);
        doc.text("FACTURA: #<?php echo $sell->sell_from_id; ?>", 420, 50);
        doc.text("FECHA: <?php echo $sell->created_at; ?>", 420, 65);
        //        doc.text("Operador:", 40, 150);
        //        doc.text("Header", 40, 30);
        //      doc.text("Header", 40, 30);
        doc.line(40, 75, 560, 75);

        <?php if ($sell->person_id != "") :
          $person = $sell->getPerson();
        ?>
          doc.text("CLIENTE: <?php echo $person->name . " " . $person->lastname; ?>", 40, 90);
          // doc.text("RUT/RFC: <?php echo $person->no; ?>", 40, 100);
          doc.text("DIRECCION: <?php echo $person->address1; ?>", 40, 105);
          doc.text("TELEFONO: <?php echo $person->phone1; ?>", 40, 120);

        <?php endif; ?>

        doc.autoTable(columns, rows, {
          overflow: 'linebreak',
          margin: {
            top: 130
          },
          afterPageContent: function(data) {
            //        doc.text("Header", 40, 30);
          }
        });

        doc.autoTable(columns2, rows3, {
          overflow: 'linebreak',
          margin: {
            top: doc.autoTableEndPosY() + 15
          },
          afterPageContent: function(data) {
            //        doc.text("Header", 40, 30);
          }
        });


        //doc.setFontsize
        //img = new Image();
        //img.src = "liberacion2.jpg";
        //doc.addImage(img, 'JPEG', 40, 10, 610, 100, 'monkey'); // Cache the image using the alias 'monkey'
        doc.setFontSize(20);
        doc.setFontSize(12);
        // doc.text("Generado por el Sistema de inventario", 40, doc.autoTableEndPosY() + 25);
        doc.save('sell-<?php echo date("d-m-Y h:i:s", time()); ?>.pdf');
        //doc.output("datauri");

      }
    </script>

    <script>
      $(document).ready(function() {
        //  $("#makepdf").trigger("click");
      });
    </script>

  <?php else : ?>
    501 Internal Error
  <?php endif; ?>
</section>