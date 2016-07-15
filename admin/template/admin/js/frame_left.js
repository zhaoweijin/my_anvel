$(document).ready(function(){
	var tt;
	$('#menu').find('.first').hover(
	  function(){
		  obj=$(this).parent().children('ul');
		  tt=setTimeout(function(){
		  $('#menu').find('.menu_hide').not(obj).slideUp('slow');
		  if( obj.css('display') == 'block'){
			  //obj.slideUp('slow');
		  }else{
			  obj.slideDown('slow');
		  }
	  },
	   300);	
	  },	
	  function(){
		  clearTimeout(tt);
	  });
	
  $('#default_menu').children('ul').slideDown('slow');  
	  
});