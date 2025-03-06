// Last Update 2025 March 06
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
		#type {
			margin-top:0.25rem;
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
			display:inline-block;191080
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
			vertical-align:top;
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
		.spe-single-pt__cell--val {
			width:40rem;
		}
		.external-link {
			word-wrap:break-word;
			overflow-x:scroll;
			overflow-y:hidden;
			scrollbar-width:none;
			white-space:nowrap;
			vertical-align:top;
		}
		.external-link::-webkit-scrollbar {
			display:none;
		}	
		.id {
			width:8rem;
			white-space:nowrap;
			padding: 0.125rem 0.75rem 0.125rem 0.25rem;
		}
		.var-name {
			width:7rem;
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
		.spe-pt__row.no-border{
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
			padding:0.125rem 0.25rem;
			/*border:solid 1px black;*/
		}
		.spe-prod-selection {
			display:inline-block;
		}
		.spe-var-label {
			padding: 0.25rem 0;
			font-weight:bold;
		}
		.spe-var-label span {
			padding: 0.25rem 0;
			font-weight:initial;
		}
		.spe-var-list-container {
			/* border: solid 1px #000000; */
			width: fit-content;
			background-color: #F4F4F4;
			line-height: 1.25rem;
			border-radius: .25rem;
			padding: 0.25rem;
		}
		.spe-var-list-item {
			padding:0 .25rem;
		}
		.spe-prod-table div div.man-stock {
			padding:0.125rem 0;
		}
		td.float-val, td.string-val {
			text-align:center;
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
			max-width:55rem;
		}
		.dropdiv-content-view-only.selectable, .dropdiv-content-view-only--container.selectable {
			background-color:#F0F0F0;
		}
	  	.dropdiv-content-view-only.dd-c-vo--copyable {
			width:unset;
			overflow:hidden;
			max-width:50rem;
	  	}
		.dd-c-vo--copyable, .dd-c-vo__copy-button {
			float:left;
		  	margin-top:1px;
			margin-bottom:1px;
			padding:1px;
		}
		.dd-c-vo--copyable {
			text-align: left;
		}
		.dd-c-vo__copy-button {
			text-align: center;
			margin-right:1rem;
			border:1px solid black;
			border-radius:3px;
			background-color:white;
		}
		.dd-c-vo__copy-button.dd-c-vo__copy-button--disabled {
			background-color:#E0E0E0;
			cursor:not-allowed;
		}
		.dd-c-vo__close-button {
			text-align: center;
			float:right;
			border:1px solid black;
			border-radius:3px;
			background-color:#FFCCCC;
			color:black;
			font-weight:bold;
			font-family:monospace;
		}
		.dd-c-vo__copy-button:hover {
			background-color:#EEEEFF;
		}
		.dd-c-vo__close-button:hover {
			background-color:#FFAAAA;
		}
		.dd-c-vo__copy-button:active {
			background-color:#CCCCFF;
		}
		.dd-c-vo__close-button:active {
			background-color:#FF8888;
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
