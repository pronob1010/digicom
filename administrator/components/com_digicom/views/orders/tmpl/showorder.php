<?php
/**
* @package			DigiCom Joomla Extension
 * @author			themexpert.com
 * @version			$Revision: 341 $
 * @lastmodified	$LastChangedDate: 2013-10-10 14:28:28 +0200 (Thu, 10 Oct 2013) $
 * @copyright		Copyright (C) 2013 themexpert.com. All rights reserved.
* @license			GNU/GPLv3
*/

defined ('_JEXEC') or die ("Go away.");

$document = JFactory::getDocument();
//$document->addStyleSheet("components/com_digicom/assets/css/digicom.css");

$k = 0;
$n = count ($this->order->products);
//Log::debug($n);
$configs = $this->configs;
$order = $this->order;
$refunds = DigiComAdminModelOrder::getRefunds($order->id);
$chargebacks = DigiComAdminModelOrder::getChargebacks($order->id);
$deleted = DigiComAdminModelOrder::getDeleted($order->id);
//Log::debug($order);
if(isset($order->id) & $order->id < 1):
	echo JText::_('DSEMPTYORDER');
	global $Itemid; 
?>

<form action="<?php echo JRoute::_("index.php?option=com_digicom&controller=orders&task=list"."&Itemid=".$Itemid); ?>" name="adminForm" method="post">
	<input type="hidden" name="option" value="com_digicom" />
	<input type="submit" value="<?php echo JText::_("DSVIEWORDERS");?>" />
</form>

<?php
else:
?>
<?php if (!empty( $this->sidebar)) : ?>
<div id="j-sidebar-container" class="">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="">
<?php else : ?>
<div id="j-main-container" class="">
<?php endif;?>
<form id="adminForm" action="index.php" name="adminForm" method="post">

	<div id="contentpane" >
		<table class="adminlist" width="100%"  border="1" cellpadding="3" cellspacing="0" bordercolor="#cccccc" style="border-collapse: collapse">
			<caption class="componentheading"><?php echo JText::_("DSMYORDER")." #".$order->id; ?>: <?php echo $order->status; ?></caption>
		</table>
		<span align="left"><b><?php echo JText::_("DSDATE")." ".date( $configs->get('time_format','DD-MM-YYYY'), $order->order_date);?></b></span>
		<br /><br />
		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th class="sectiontableheader"></th>
					<th class="sectiontableheader"  >
						<?php echo JText::_('DSPROD');?>
					</th>
					
					<!--
					<th class="sectiontableheader"  >
						<?php echo JText::_('DSLICENSEID');?>
					</th>
					-->

					<th class="sectiontableheader"  >
						<?php echo JText::_('DSPRICE');?>
					</th>


					<th class="sectiontableheader"  >
						<?php echo JText::_('DSDISCOUNT');?>
					</th>

					<th class="sectiontableheader" >
						<?php echo JText::_('DSTOTAL');?>
					</th>

				</tr>
			</thead>

				<tbody>

				<?php 
				$oll_courses_total = 0;
				//for ($i = 0; $i < $n; $i++):
				$i = 0;
				foreach ($order->products as $key=>$prod):
					if(!isset($prod->id)) break;
					$id = $order->id;
					if (isset($prod->orig_fields) && count ($prod->orig_fields) > 0){
						foreach ($prod->orig_fields as $j => $z) {
							$val = explode(",", $z->optioname);
							if (isset($val[1]) && strlen (trim($val[1])) > 0) {
								$prod->price += floatval(trim($val[1]));
								$prod->amount_paid += floatval(trim($val[1]));
							}
						}
					}
					if (!isset($prod->currency)) {
						$prod->currency = $configs->get('currency','USD');
					}
					
					$licenseid = $prod->id;
					//print_r($prod);die;
					$refund = DigiComAdminModelOrder::getRefunds($order->id, $prod->licenseid);
					$chargeback = DigiComAdminModelOrder::getChargebacks($order->id, $prod->licenseid);
					$cancelled = DigiComAdminModelOrder::isLicenseDeleted($prod->licenseid);?>
					<tr class="row<?php echo $k;?> sectiontableentry<?php echo ($i%2 + 1);?>">
						<td style="<?php echo $cancelled == 3 ? 'text-decoration: line-through;' : '';?>"><?php echo $i+1; ?></td>
						<td style="<?php echo $cancelled == 3 ? 'text-decoration: line-through;' : '';?>">
							<?php echo $prod->name;?>
							<?php
							if(!empty($prod->orig_fields)){
								foreach($prod->orig_fields as $attr){
									echo "<br/>".$attr->fieldname.":".$attr->optioname;
								}
							} ?>
						</td>
						<!--
						<td style="<?php echo $cancelled == 3 ? 'text-decoration: line-through;' : '';?>"><?php echo $prod->licenseid;?></td>
						-->
						<td style="<?php echo $cancelled == 3 ? 'text-decoration: line-through;' : '';?>"><?php
							$price = $prod->price - $refund - $chargeback;
							echo DigiComAdminHelper::format_price($prod->price, $prod->currency, true, $configs);
							
							if ($refund > 0)
							{
								echo '&nbsp;<span style="color:#ff0000;"><em>('.JText::_("LICENSE_REFUND")." - ".DigiComAdminHelper::format_price($refund, $prod->currency, true, $configs).')</em></span>';
							}
							if ($chargeback > 0)
							{
								echo '&nbsp;<span style="color:#ff0000;"><em>('.JText::_("LICENSE_CHARGEBACK")." - ".DigiComAdminHelper::format_price($chargeback, $prod->currency, true, $configs).')</em></span>';
							} ?>
						</td>
						<td style="<?php echo $cancelled == 3 ? 'text-decoration: line-through;' : '';?>">
							<?php
								echo DigiComAdminHelper::format_price($prod->price - $prod->amount_paid, $prod->currency, true, $configs);
								$oll_courses_total += $prod->price;
							?>
						</td>
						<td style="<?php echo $cancelled == 3 ? 'text-decoration: line-through;' : '';?>"><?php
							$prod->amount_paid = $prod->amount_paid - $refund - $chargeback;
							echo DigiComAdminHelper::format_price($prod->amount_paid, $prod->currency, true, $configs);?>
						</td>
					</tr><?php
					$k = 1 - $k;
					$i++;
				endforeach; ?>

				<tr style="border-style:none;"><td style="border-style:none;" colspan="6"><hr /></td></tr>
				<tr><td colspan="3" ></td>
					<td style="font-weight:bold"><?php echo JText::_("DSSUBTOTAL");?></td>
					<td>
						<?php 
							echo DigiComAdminHelper::format_price($oll_courses_total, $order->currency, true, $configs);
						?>
					</td></tr>
				<?php if ($order->shipping > 0):?>
				<tr><td colspan="3"></td>
					<td style="font-weight:bold"><?php echo JText::_("DSSHIPPING");?></td>
					<td><?php echo DigiComAdminHelper::format_price($order->shipping, $order->currency, true, $configs);?></td></tr>
				<?php endif; ?>
				<tr><td colspan="3"></td>
					<td style="font-weight:bold"><?php echo JText::_("DSTAX");?></td>
					<td><?php echo DigiComAdminHelper::format_price($order->tax, $order->currency, true, $configs);?></td></tr>
				<tr><td colspan="3"></td>
					<td style="font-weight:bold"><?php echo JText::_("VIEWCONFIGSHOWCPROMO");?> "<?php echo $order->promocode; ?>"</td>
					<td><?php echo DigiComAdminHelper::format_price($order->promocodediscount, $order->currency, true, $configs);?></td></tr>
				<?php if ($refunds > 0):?>
				<tr>
					<td colspan="3"></td>
					<td style="font-weight:bold;color:#ff0000;"><?php echo JText::_("LICENSE_REFUNDS");?></td>
					<td style="color:#ff0000;"><?php echo DigiComAdminHelper::format_price($refunds, $order->currency, true, $configs); ?></td>
				</tr>
				<?php endif;?>
				<?php if ($chargebacks > 0):?>
				<tr>
					<td colspan="3"></td>
					<td style="font-weight:bold;color:#ff0000;"><?php echo JText::_("LICENSE_CHARGEBACKS");?></td>
					<td style="color:#ff0000;"><?php echo DigiComAdminHelper::format_price($chargebacks, $order->currency, true, $configs); ?></td>
				</tr>
				<?php endif;?>
				<?php if ($deleted > 0):?>
				<tr>
					<td colspan="3"></td>
					<td style="font-weight:bold;color:#ff0000;"><?php echo JText::_("DELETED_LICENSES");?></td>
					<td style="color:#ff0000;"><?php echo DigiComAdminHelper::format_price($deleted, $order->currency, true, $configs); ?></td>
				</tr>
				<?php endif;?>
				<tr><td colspan="3"></td>
						<td style="font-weight:bold"><?php echo JText::_("DSTOTAL");?></td>
					<td>
						<?php
							$value = $order->amount_paid;
							if($value == "-1"){
								$value = $order->amount;
							}
							$value = $value - $refunds - $chargebacks;
							echo DigiComAdminHelper::format_price($value, $order->currency, true, $configs);
						?>
					</td>
				</tr>
				</tbody>


			</table>

		</div>

		<input type="hidden" name="option" value="com_digicom" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="controller" value="Orders" />
	</form>
	</div>
<?php

endif;