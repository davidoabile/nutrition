jQuery(document).ready( function() {
	if(jQuery(window).width()<=600) {
		jQuery('#main_dashboard_td_right .entry-edit:first-child').width(jQuery('.wrapper').width()-4);
		jQuery('#main_dashboard_td_right #dashboard_diagram_totals div:first-child').width(jQuery('.wrapper').width()-2);
		jQuery('#diagram_tab_content').next().css('margin',0);
		jQuery('#diagram_tab_content').next().next().css('margin',0);
	}
});
jQuery(window).resize( function() {
	if(jQuery(window).width()<=600) {
		jQuery('#main_dashboard_td_right .entry-edit:first-child').width(jQuery('.wrapper').width()-4);
		jQuery('#main_dashboard_td_right #dashboard_diagram_totals div:first-child').width(jQuery('.wrapper').width()-2);
		jQuery('#diagram_tab_content').next().css('margin',0);
		jQuery('#diagram_tab_content').next().next().css('margin',0);
	}
	else {
		jQuery('#main_dashboard_td_right .entry-edit:first-child').width(jQuery('.dashboard-container').width()-jQuery('#main_dashboard_td_left').width()-94);
	}
});
