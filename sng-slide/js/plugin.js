jQuery(function() {
  jQuery(".slider").carouFredSel({
    transition: true,
    prev: '.slidprev',
    next: '.slidnext',
    pagination: ".paginationSlider",
    responsive: true,
    width: '100%',
    /*scroll : { 
         items            : 1, 
         easing           : "elastic", 
         duration         : 10000, 
         pauseOnHover     : true 
     } ,
     timeoutDuration: 5000*/
  });
});