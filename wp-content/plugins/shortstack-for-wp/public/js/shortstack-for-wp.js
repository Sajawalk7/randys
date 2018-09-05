/*
 * Handles the responsive sizing for the iframe.
 */

 (function(){

   function loadEmbed(){
     var iframes = document.getElementsByClassName('campaign_embed_responsive');
     for(var i = 0; i < iframes.length; i++) {
       iFrameResize({ enablePublicMethods: true }, iframes[i]);
     }
   }

   loadEmbed();

 })();
