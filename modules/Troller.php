<?php/*
;******************************
;* (c) 2010, Stephan Gärtner  *
;* Using permission just for  *
;* SteGaSoft WordPress Plugin *
;* WP-Ticker                  *
;*                            *
;* TSCRIPTNAME: Roller        *
;******************************

[INFO]
name = Roller
hint = "Hint: Vertical in- out-rolling content"
code = "function jTickerRoller(id){jQuery('#ticker_content_'+id+' div:first').slideUp(out_time[id],function(){jQuery('#ticker_content_'+id+' div:first').remove().appendTo('#ticker_content_'+id);jQuery('#ticker_content_'+id+' div:first').slideDown(in_time[id]);});}function jTickerStartRoller(id){fade_timer[id]=window.setInterval('jTickerRoller('+id+')',show_time[id]);}function jTickerEndRoller(id){window.clearInterval(fade_timer[id]);}"
*/?>