

<?php

$this->pdf = new PdfInventario($orientation = 'L', $unit = 'mm', $format = 'A4');
// Agregamos una página
$this->pdf->AddPage();
// Define el alias para el número de página que se imprimirá en el pie
$this->pdf->AliasNbPages();

#$this->pdf->SetFont('Times','',10) # TAMAÑO DE LA FUENTE
$this->pdf->SetFont('Arial','B',15);
$this->pdf->SetFillColor(157,188,201); # COLOR DE BORDE DE LA CELDA
$this->pdf->SetTextColor(77,77,77); # COLOR DEL TEXTO
$this->pdf->SetMargins(15,15,10); # MÁRGENES DEL DOCUMENTO

$this->pdf->SetFillColor(255,255,255);
$this->pdf->SetFont('Arial','B',14);
$this->pdf->Ln(20);
$this->pdf->SetFont('Arial','',8);

# id de la orden
if(isset($order['order_invoice'][0]['id_order'])){
	$id_order = $order['order_invoice'][0]['id_order'];
}else{
	$id_order = "N/A";
}

// Conversión de la fecha del pedido
if(isset($order['order_invoice']) && count($order['order_invoice']) > 0){
	$fecha_pedido = explode(" ", $order['order_invoice'][0]['date_add']);
	$fecha_pedido = explode("-", $fecha_pedido[0]);
	$fecha_pedido = $fecha_pedido[2]."/".$fecha_pedido[1]."/".$fecha_pedido[0];
}else{
	$fecha_pedido = "";
}

// Preparación de la fecha actual
$now_delivery_date = $order['order'][0]['date_add'];
$delivery_date = date("d/m/Y", strtotime($now_delivery_date));

// Fecha y número de factura
$this->pdf->SetFont('Arial','B',8);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Cell(12,4,"Fecha: ",'',0,'L',1);
$this->pdf->SetFont('Arial','',8);
$this->pdf->Cell(16,4,"$fecha_pedido",'',0,'L',1);
$this->pdf->Cell(125,4,"",'',0,'L',1);
$this->pdf->SetFont('Arial','B',8);
if(isset($order['order_invoice']) && count($order['order_invoice']) > 0){
	if(isset($order['order'][0]['invoice_number'])){
		$num_correlative = $order['order'][0]['invoice_number'];
	}else{
		$num_correlative = "N/A";
	}
	$delivery_number = str_pad($num_correlative, 6, "0", STR_PAD_LEFT);
	$this->pdf->Cell(32,4,"FACTURA: ".$delivery_number,'',1,'R',1);
}else{
	$this->pdf->Cell(32,4,"FACTURA: ",'',1,'R',1);
}

# Ttile para la salida del PDF
$title = "factura_".$id_order."_".$order['order'][0]['invoice_number'];
$this->pdf->SetTitle($title);

// Razón social
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Cell(32,4,utf8_decode("Nombre o razón social: "),'',0,'L',1);
$this->pdf->SetFont('Arial','',8);
if(isset($order['order'][0]['address_delivery']) && count($order['order'][0]['address_delivery']) > 0){
	//$width_business_name = strlen($order['order'][0]['address_invoice'][0]['company'])+15;  // De esta forma calculamos el espacio a asignarle a la celda (longitud de la cadena + 15)
	// Direccion de entrega (address_delivery)
	if($order['order'][0]['address_delivery'][0]['address1']!= $order['order'][0]['address_invoice'][0]['address1']){
		$vat_number = "address_invoice";
		$field_ident = "dni";
		$this->pdf->Cell(71,4,utf8_decode($order['order'][0]['address_invoice'][0]['company']),'',0,'L',1);
	}else{
		$vat_number = "address_delivery";
		$field_ident = "vat_number";
		$this->pdf->Cell(71,4,utf8_decode($order['order'][0]['address_delivery'][0]['company']),'',0,'L',1);
	}
}/*else{
	$width_business_name = 0;
	$this->pdf->Cell($width_business_name,4,"",'',0,'L',1);
}*/
// Rif
$this->pdf->SetFont('Arial','B',8);
$this->pdf->Cell(13,4,"CI o RIF: ",'',0,'L',1);
$this->pdf->SetFont('Arial','',8);
if(isset($order['order'][0]['customer']) && count($order['order'][0]['customer']) > 0){
	$width_rif = strlen($order['order'][0]['address_delivery'][0]['vat_number']);  // De esta forma calculamos el espacio a asignarle a la celda (longitud de la cadena + 15)
	$this->pdf->Cell($width_rif,4,$order['order'][0][$vat_number][0][$field_ident],'',1,'L',1);
}else{
	$width_rif = 0;
	$this->pdf->Cell($width_rif,4,"",'',1,'L',1);
}
// Dirección fiscal
$this->pdf->SetFont('Arial','B',8);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Cell(23,4,utf8_decode("Dirección fiscal: "),'',0,'L',1);
$this->pdf->SetFont('Arial','',8);
//$width_address = strlen($order['order'][0]['address_invoice'][0]['address1'])+30;  // De esta forma calculamos el espacio a asignarle a la celda (longitud de la cadena + 30)
// Direccion de facturación (address_invoice)
$this->pdf->Cell(77,4,utf8_decode($order['order'][0]['address_invoice'][0]['address1']),'',0,'L',1);
$this->pdf->Ln(5);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Cell(180,4,utf8_decode($order['order'][0]['address_invoice'][0]['address2']),'',1,'L',1);
// Número de teléfono
$this->pdf->SetFont('Arial','B',8);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Cell(15,4,utf8_decode("Teléfono: "),'',0,'L',1);
$this->pdf->SetFont('Arial','',8);
$this->pdf->Cell(15,4,$order['order'][0]['address_invoice'][0]['phone'],'',1,'L',1);

$this->pdf->Ln(5);

// Títulos
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Cell(30,4,utf8_decode(""),'',0,'C',1);
$this->pdf->SetFillColor(77,77,77);
$this->pdf->SetTextColor(255,255,255); # COLOR DEL TEXTO
$this->pdf->SetFont('Arial','B',8);
$this->pdf->Cell(20,4,utf8_decode("Cant."),'B',0,'C',1);
$this->pdf->Cell(75,4,"Producto / Referencia",'B',0,'L',1);
$this->pdf->Cell(30,4,"Precio unitario",'B',0,'R',1);
$this->pdf->Cell(30,4,"Total",'B',1,'R',1);

$this->pdf->SetFillColor(255,255,255);
$this->pdf->SetTextColor(77,77,77); # COLOR DEL TEXTO
$this->pdf->SetFont('Arial','',8);
$j = 1;  // Contador de registros
$subtotal = 0;  // Acumulador para el subtotal
$tasa_iva_decimals = explode(".", (string)number_format($order['order'][0]['carrier_tax_rate'], 2));
$tasa_iva_decimals = $tasa_iva_decimals[1];
if((int)$tasa_iva_decimals > 0){
	$tasa_iva = number_format($order['order'][0]['carrier_tax_rate'], 2);  // Tasa de impuesto de la orden
}else{
	$tasa_iva = number_format($order['order'][0]['carrier_tax_rate'], 0);  // Tasa de impuesto de la orden
}
$iva = 0;  // Monto en impuestos
$total = 0;  // Monto total

if(isset($order['order_detail']) && count($order['order_detail']) > 0){
	
	foreach($order['order_detail'] as $order_detail){
		$this->pdf->Cell(5,4,"",'',0,'L',0);
		$this->pdf->SetFillColor(255,255,255);
		$this->pdf->Cell(30,5,"",'',0,'C',1);
		// Aplicamos una variación de color de fondo a las filas
		if($j >= 2 && $j%2 == 0){
			$this->pdf->SetFillColor(221,221,221);
		}else{
			$this->pdf->SetFillColor(255,255,255);
		}
		$this->pdf->Cell(20,6,"".$order_detail['product_quantity'],'',0,'C',1);
		if(strlen($order_detail['product_name']) > 50){
			$this->pdf->Cell(75,6,utf8_decode(substr($order_detail['product_name'], 0, 55)."..."),'',0,'L',1);
		}else{
			$this->pdf->Cell(75,6,utf8_decode($order_detail['product_name']),'',0,'L',1);
		}
		$this->pdf->Cell(30,6,"".number_format($order_detail['unit_price_tax_excl'], 2, ',', '.')." Bs",'',0,'R',1);
		$this->pdf->Cell(30,6,"".number_format($order_detail['unit_price_tax_excl']*$order_detail['product_quantity'], 2, ',', '.')." Bs",'',1,'R',1);
		
		$subtotal += ($order_detail['unit_price_tax_excl']*$order_detail['product_quantity']);
		
		$j++;
	}
	
}


// Subtotal
$this->pdf->SetFillColor(255,255,255);
$this->pdf->SetTextColor(77,77,77); # COLOR DEL TEXTO
$this->pdf->SetFont('Arial','B',8);
$this->pdf->Cell(125,6,"",'',0,'C',1);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Cell(29.5,6,"Subtotal",'',0,'R',1);
$this->pdf->SetFillColor(255,255,255);
$this->pdf->SetFont('Arial','',8);
$this->pdf->Cell(31,6,"".number_format($subtotal, 2, ',', '.')." Bs",'',1,'R',1);

// Descuento
$total_discounts_tax_excl = $order['order'][0]['total_discounts_tax_excl'];
$sub_total_desc = (float)$subtotal - (float)$total_discounts_tax_excl;
$mount_discounts = $sub_total_desc * (float)$tasa_iva / 100;

if($total_discounts_tax_excl > 0){
	
	$iva_discounts =  $total_discounts_tax_excl *100 / $subtotal;

	$this->pdf->SetFillColor(255,255,255);
	$this->pdf->SetTextColor(77,77,77); # COLOR DEL TEXTO
	$this->pdf->SetFont('Arial','B',8);
	$this->pdf->Cell(125,6,"",'',0,'C',1);
	$this->pdf->Cell(5,4,"",'',0,'L',1);
	$this->pdf->Cell(29.5,6,"Descuento(".number_format($iva_discounts, 0, '', '')."%)",'',0,'R',1);
	$this->pdf->SetFillColor(255,255,255);
	$this->pdf->SetFont('Arial','',8);
	$this->pdf->Cell(31,6,"-".number_format($total_discounts_tax_excl, 2, ',', '.')." Bs",'',1,'R',1);

	// Descuento
	$this->pdf->SetFillColor(255,255,255);
	$this->pdf->SetTextColor(77,77,77); # COLOR DEL TEXTO
	$this->pdf->SetFont('Arial','B',8);
	$this->pdf->Cell(125,6,"",'',0,'C',1);
	$this->pdf->Cell(5,4,"",'',0,'L',1);
	$this->pdf->Cell(29.5,6,"Subtotal-desc",'',0,'R',1);
	$this->pdf->SetFillColor(255,255,255);
	$this->pdf->SetFont('Arial','',8);
	$this->pdf->Cell(31,6,"".number_format($sub_total_desc, 2, ',', '.')." Bs",'',1,'R',1);
}



// IVA
$iva = $subtotal * (float)$tasa_iva / 100;
$this->pdf->SetFillColor(255,255,255);
$this->pdf->SetTextColor(77,77,77); # COLOR DEL TEXTO
$this->pdf->SetFont('Arial','B',8);
$this->pdf->Cell(125,6,"",'',0,'C',1);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Cell(29.5,6,"IVA(".$tasa_iva."%)",'',0,'R',1);
$this->pdf->SetFillColor(255,255,255);
$this->pdf->SetFont('Arial','',8);

if($mount_discounts > 0){
	$iva_total = $mount_discounts;
}else{
	$iva_total = $iva;
}

$this->pdf->Cell(31,6,"".number_format($iva_total, 2, ',', '.')." Bs",'',1,'R',1);
// Total
//~ $total = $subtotal + $iva;  // Monto anterior calculado desde el documento
$total = $order['order'][0]['total_paid_tax_incl'];
$this->pdf->SetFillColor(255,255,255);
$this->pdf->SetTextColor(77,77,77); # COLOR DEL TEXTO
$this->pdf->SetFont('Arial','B',8);
$this->pdf->Cell(125,6,"",'',0,'C',1);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Cell(29.5,6,"Total",'',0,'R',1);
$this->pdf->SetFillColor(255,255,255);
$this->pdf->SetFont('Arial','',8);
$this->pdf->Cell(31,6,"".number_format($total, 2, ',', '.')." Bs",'',1,'R',1);


// Número de pedido
$this->pdf->SetY(51);
$this->pdf->SetTextColor(77,77,77); # COLOR DEL TEXTO
$this->pdf->SetFont('Arial','B',7);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Write(5,utf8_decode("Número de pedido:"),'',1,'C',0);
$this->pdf->SetY(55);
$this->pdf->SetFont('Arial','',8);
$reference = $order['order'][0]['reference'];
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Write(5,"$id_order-$reference",'',1,'C',0);

// Fecha de pedido
$this->pdf->SetY(62);
$this->pdf->SetTextColor(77,77,77); # COLOR DEL TEXTO
$this->pdf->SetFont('Arial','B',7);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Write(6,utf8_decode("Fecha de pedido:"),'',1,'R',0);
$this->pdf->SetY(66);
$this->pdf->SetFont('Arial','',8);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Write(5,$delivery_date,'',1,'R',0);

// Método de pago
$this->pdf->SetY(73);
$this->pdf->SetTextColor(77,77,77); # COLOR DEL TEXTO
$this->pdf->SetFont('Arial','B',7);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Write(5,utf8_decode("Método de pago:"),'',1,'C',0);
$this->pdf->SetY(77);
$this->pdf->SetFont('Arial','',8);
$this->pdf->Cell(5,4,"",'',0,'L',1);
$this->pdf->Write(5,utf8_decode($order['order'][0]['payment']),'',1,'C',0);

//~ $this->pdf->Cell(125,1,"",'',1,'R',1);  // Cierre de bloque de productos

// Salida del Formato PDF

$this->pdf->Output("factura_".$id_order."_".$order['order'][0]['invoice_number'].".pdf", 'I');
