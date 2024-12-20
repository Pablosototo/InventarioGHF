<?php
$clients = PersonData::getClients();
$users = UserData::getAll();
?>
<section class="content">
<div class="row">
	<div class="col-md-12">
	<h1>Reportes de Ventas</h1>

						<form>
						<input type="hidden" name="view" value="sellreports">
<div class="row">
<div class="col-md-2">

<select name="user_id" class="form-control">
	<option value="">-- VENDEDOR --</option>
	<?php foreach($users as $p):?>
	<option value="<?php echo $p->id;?>"><?php echo $p->name;?></option>
	<?php endforeach; ?>
</select>

</div>
<div class="col-md-3">

<select name="client_id" class="form-control">
	<option value="">-- CLIENTE --</option>
	<?php foreach($clients as $p):?>
	<option value="<?php echo $p->id;?>"><?php echo $p->name;?></option>
	<?php endforeach; ?>
</select>

</div>

<div class="col-md-3">
<input type="date" name="sd" value="<?php if(isset($_GET["sd"])){ echo $_GET["sd"]; }?>" class="form-control">
</div>
<div class="col-md-3">
<input type="date" name="ed" value="<?php if(isset($_GET["ed"])){ echo $_GET["ed"]; }?>" class="form-control">
</div>

<div class="col-md-1">
<button type="submit" class="btn btn-primary btn-block"><i class="fa fa-file-text"></i></button>
</div>

</div>

</form>

	</div>
	</div>
<br><!--- -->
<div class="row">
	
	<div class="col-md-12">
		<?php if(isset($_GET["sd"]) && isset($_GET["ed"]) ):?>
<?php if($_GET["sd"]!=""&&$_GET["ed"]!=""):?>
			<?php 
			$operations = array();

			if($_GET["client_id"]=="" && $_GET["user_id"]==""){
			$operations = SellData::getAllByDateOp($_GET["sd"],$_GET["ed"],2);
			}
			else if($_GET["client_id"]=="" && $_GET["user_id"]!=""){
			$operations = SellData::getAllByDateOpByUserId($_GET["user_id"],$_GET["sd"],$_GET["ed"],2);
			}
			else if($_GET["client_id"]!="" && $_GET["user_id"]==""){
			$operations = SellData::getAllByDateBCOp($_GET["client_id"],$_GET["sd"],$_GET["ed"],2);
			}else{
			$operations = SellData::getAllByDateBCOpByUserId($_GET["user_id"],$_GET["client_id"],$_GET["sd"],$_GET["ed"],2);
			} 


			 ?>

			 <?php if(count($operations)>0):?>
			 	<?php $supertotal = 0; ?>
<a onclick="thePDF()" id="makepdf" class="btn btn-default" class="">PDF (.pdf)</a>

<a href="./report/sellreports-xlsx.php?client_id=<?php echo $_GET["client_id"]; ?>&sd=<?php echo $_GET["sd"]; ?>&ed=<?php echo $_GET["ed"]; ?>" class="btn btn-default">Excel (.xlsx)</a><br><br>

<div class="box box-primary">
<table class="table table-bordered">
	<thead>
		<th>Id</th>
		<th>Subtotal</th>
		<th>Descuento</th>
		<th>Total</th>
		<th>Cliente</th>
		<th>Vendedor</th>
		<th>Fecha</th>
	</thead>
<?php foreach($operations as $operation):?>
	<tr>
		<td><?php echo $operation->ref_id; ?></td>
		<td><?php echo Core::$symbol; ?> <?php echo number_format($operation->total,2,'.',','); ?></td>
		<td><?php echo Core::$symbol; ?> <?php echo number_format($operation->discount,2,'.',','); ?></td>
		<td><?php echo Core::$symbol; ?> <?php echo number_format($operation->total-$operation->discount,2,'.',','); ?></td>
	<td> <?php if($operation->person_id!=null){$c= $operation->getPerson();echo $c->name." ".$c->lastname;} ?> </td>
	<td> <?php if($operation->user_id!=null){$c= $operation->getUser();echo $c->name." ".$c->lastname;} ?> </td>
		<td><?php echo $operation->created_at; ?></td>
	</tr>
<?php
$supertotal+= ($operation->total-$operation->discount);
 endforeach; ?>

</table>
</div>
<h1>Total de ventas: <?php echo Core::$symbol; ?> <?php echo number_format($supertotal,2,'.',','); ?></h1>
<?php if(isset($_GET["user_id"]) && $_GET["user_id"]!=""):
$uy = UserData::getById($_GET["user_id"]);
?>
<!--- <h1>Comision (<//?php echo $uy->comision; ?>%): <//?php echo Core::$symbol; ?> <//?php echo number_format($supertotal*($uy->comision/100),2,'.',','); ?></h1>-->

<?php endif; ?>
<script type="text/javascript">
        function thePDF() {
var doc = new jsPDF('p', 'pt');
        doc.setFontSize(26);
        doc.text("<?php echo ConfigurationData::getByPreffix("company_name")->val;?>", 40, 65);
        doc.setFontSize(18);
        doc.text("REPORTE DE VENTAS", 40, 80);
        doc.setFontSize(12);
        doc.text("Usuario: <?php echo Core::$user->name." ".Core::$user->lastname; ?>  -  Fecha: <?php echo date("d-m-Y h:i:s");?> ", 40, 90);
var columns = [
    {title: "Id", dataKey: "id"}, 
    {title: "Subtotal", dataKey: "subtotal"}, 
    {title: "Descuento", dataKey: "discount"}, 
    {title: "Total", dataKey: "total"}, 
    {title: "Cliente", dataKey: "client"}, 
    {title: "Vendedor", dataKey: "vendor"}, 
    {title: "Fecha", dataKey: "created_at"}, 
];
var rows = [
  <?php foreach($operations as $operation):
  ?>
    {
      "id": "<?php echo $operation->id; ?>",
      "subtotal": "<?php echo Core::$symbol; ?> <?php echo number_format($operation->total,2,'.',','); ?>",
      "discount": "<?php echo Core::$symbol; ?> <?php echo number_format($operation->discount,2,'.',','); ?>",
      "total": "<?php echo Core::$symbol; ?> <?php echo number_format($operation->total-$operation->discount,2,'.',','); ?>",
      "client": "<?php if($operation->person_id!=null){$c= $operation->getPerson();echo $c->name." ".$c->lastname;} ?>",
      "vendor": "<?php if($operation->user_id!=null){$c= $operation->getUser();echo $c->name." ".$c->lastname;} ?>",
      "created_at": "<?php echo $operation->created_at; ?>",
      },
 <?php endforeach; ?>
];
doc.autoTable(columns, rows, {
    theme: 'grid',
    overflow:'linebreak',
    styles: { 
        fillColor: <?php echo Core::$pdf_table_fillcolor;?>
    },
    columnStyles: {
        id: {fillColor: <?php echo Core::$pdf_table_column_fillcolor;?>}
    },
    margin: {top: 100},
    afterPageContent: function(data) {
    }
});
doc.setFontSize(18);
doc.text("TOTAL : <?php echo Core::$symbol; ?> <?php echo number_format($supertotal,2,'.',','); ?> <?php if(isset($_GET['user_id']) && $_GET['user_id']!='') {$uy = UserData::getById($_GET["user_id"]); echo ' - Comision ('.$uy->comision.'%): '.Core::$symbol.' '.number_format($supertotal*($uy->comision/100),2,'.',',') ;}?>", 40, doc.autoTableEndPosY()+25);
doc.setFontSize(12);
doc.text("<?php echo Core::$pdf_footer;?>", 40, doc.autoTableEndPosY()+45);
<?php 
$con = ConfigurationData::getByPreffix("report_image");
if($con!=null && $con->val!=""):
?>
var img = new Image();
img.src= "storage/configuration/<?php echo $con->val;?>";
img.onload = function(){
doc.addImage(img, 'PNG', 495, 20, 60, 60,'mon');	
doc.save('sellreports-<?php echo date("d-m-Y h:i:s",time()); ?>.pdf');
}
<?php else:?>
doc.save('sellreports-<?php echo date("d-m-Y h:i:s",time()); ?>.pdf');
<?php endif; ?>
}
</script>



<?php else:
// si no hay operaciones
?>
<script>
	$("#wellcome").hide();
</script>
<div class="jumbotron">
	<h2>No hay operaciones</h2>
	<p>El rango de fechas seleccionado no proporciono ningun resultado de operaciones.</p>
</div>

			 <?php endif; ?>
<?php else:?>
<script>
	$("#wellcome").hide();
</script>
<div class="jumbotron">
	<h2>Fecha Incorrectas</h2>
	<p>Puede ser que no selecciono un rango de fechas, o el rango seleccionado es incorrecto.</p>
</div>
<?php endif;?>

		<?php endif; ?>
	</div>
</div>

<br><br><br><br>
</section>