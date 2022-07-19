function spe_load_stylesheet($style_settings_arr) {
	?>
	<style>
		h4 {
			margin:0.25rem 0;
		}
		.spe-main-container, .spe-settings-container  {
			display:inline-block;
			color:#000;
			margin:0.5rem 0;
			padding:0.5rem;
			width:96%;
		}
		.spe-main-container {
			background:#FFF;
		}
		.load-status-text {
			font-size:1rem;padding:0.125rem 0.5rem;
		}
		.load-status-text__trash {
			background-color:#FFCCCC;
		}
		.load-status-text__normal {
			background-color:#CCFFCC;
		}
		.desc-text {
			padding:0.125rem 0.5rem;
			background-color:#E0E0E0;
		}
		.page-num {
			text-align:center;
			padding:0.125rem 0.5rem;
			background-color:#E0E0E0;
			display:inline-block;
			border:1px solid white;
		}
		.current-page {
			font-weight:bold;
		}
		.l-red, .spe_error {
			background-color:#FFCCCC;
		}
		.l-blue {
			background-color:#CCCCFF;
		}
		.spe-prod-table {
			 font-size:0.9rem;
		}
		.spe-pt__row {
			display:inline-block;
			border-bottom:1px solid black;
		}
		.spe-pt__cell {
			text-align:left;
			padding:0.125rem 0.5rem;
			/* background-color:#FFFFFF; */
			border-bottom:none;
			display:inline-block;
		}
		.spe-pt__cell--id {
			background-color:#F0F0F0;
			min-width:5rem;
		}
		.spe-pt__cell--type {
			min-width:<?=$style_settings_arr['typeColumnWidth'];?>rem;
		}
		.spe-pt__cell--status {
			width:3rem;
		}
		.spe-pt__cell--title {
			width:26rem;
			white-space:nowrap;
			overflow:hidden;
			vertical-align:top;
		}
		.id {
			width:8rem;
			white-space:nowrap;
			padding: 0.125rem 0.75rem 0.125rem 0.25rem;
		}
		.sku {
			width:30rem;
		}
	    .attribute {
			width:17rem;
		}
	  	.sku, .attribute {
			white-space:nowrap;
			overflow:hidden;
			vertical-align:top;
		}
		.price{
			min-width:4.25rem;
		}
		.spe-prod-table--info-label .spe-pt__cell.man-stock {
			font-size:0.8rem;
		}
		div.man-stock {
			min-width:6rem;
		}
		.product-cat {
			min-width:11rem;
		}
		.spe-prod-table div.no-border div.man-stock{
			padding:0;
		}
		div.stock{
			min-width:5rem;
		}
		.spe-dropdown--vis {
			width:8rem;
		}
	  	.spe-dropdown--status {
			width:3rem;
		}
		.id.spe-dropdown--status {
			width:8rem;
		}
		div.attribute {
			min-width:17rem;
		}
		.spe-prod-table div.no-border{
			border-bottom:none;
		}
		div div.center {
			text-align:center;
		}
		.spe-prod-table--info-label {
			background-color:#E0E0E0;
		}
		.spe-prod-table__draft {
			background-color:#BBC0BB;
		}
		.spe-prod-table__private {
			background-color:#D0E0DD;
		}
		.spe-prod-table__trash {
			background-color:#FFCCCC;
		}
		.inactive {
			background-color:#E0E0E0;
	 	}
		.negative {
			color:#FF0000;
		}
	 	.increased {
			background-color:#CCFFCC;
	 	}
		.decreased {
			background-color:#FFCCCC;
	 	}
		.bold {
			font-weight:bold;
		}
		.editing-mode {
			box-shadow:inset 0 0 3px #000;
			background-color:#CCCCFF;
		}
		summary::marker {
			display: none;
		}
		details > summary {
			list-style:none;
		}
		div.edited-product-info-box {
			background-color:#F0F0F0;
			padding:0.125rem 0;
		}
		.spe-prod-info {
			padding: 0.25rem;
		}
		.spe-prod-selection {
			display:inline-block;
		}
		.spe-var-label {
			padding: 0.25rem 0;
		}
		.spe-prod-table div div.man-stock {
			padding:0.125rem 0;
		}
		.dropdiv-content {
			background-color:#CCFFDD;
			overflow:auto;
			z-index:1;
		}
		.spe-prod-table div.dropdiv-content, .spe-dropdown-parent div.dropdiv-content {
			display:none;
			position:absolute;
			margin-left: -.5rem;
			box-shadow: 0.25rem 0.25rem 0.25rem 0 rgba(0, 0, 0, .4);
			border: 1px solid black;
			border-radius: .25rem;
			padding:0.25rem;
		}
		.dropdiv-content.man-stock {
			margin:0.125rem 0;
		}
		.spe-dropdown-parent .dropdiv-content.show {
			display:block;
		}
		.edited {
			background-color:#CCCCFF;
		}
		.dropdiv-content-option, .dropdiv-content-view-only {
			width:100%;
			margin:0;
			display:inline-block;
		}
		.dropdiv-content-option:hover {
			background-color:#C0F0D0;
		}
		.dropdiv-content-view-only, .dropdiv-content-view-only--container {
			background-color:#E8FFF8;
			z-index:0;
		}
		.dropdiv-content--standalone {
			display:none;
			position:absolute;
			box-shadow: 0.25rem 0.25rem 0.25rem 0 rgba(0, 0, 0, .4);
			border: 1px solid black;
			border-radius: .25rem;
			padding:0.25rem;
		}
		.dropdiv-content--standalone.show {
			display:inline-block;
		}
		.menu_order_input::-webkit-outer-spin-button, .menu_order_input::-webkit-inner-spin-button {
			-webkit-appearance: none;
			margin: 0;
		}
		.menu_order_input[type=number] {
			-moz-appearance: text;
		}
	</style>
	<?php
}
