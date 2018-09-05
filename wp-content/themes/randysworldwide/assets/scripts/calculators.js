(function($) {
  /* jshint ignore:start */

  /*! Calculators.js v0.0.1 | (c) Fresh consulting  */
  //Setup calculateRPM
  //Global variables
  var calValidation = {
      calRegexp:/^[0-9]+(\.[0-9]+)?$/,
      isDecimal:function(txt){
          return this.calRegexp.test(txt);
      }
  }
  
  //// https://www.ringpinion.com/calculators/Calc_RPM.aspx
  //Calulate RPM
  //Set name at inputs
  //Source ratio,tireHeight,speed
  //Result auto,aod( Auto overdrive),fiveSpeed
  var CalculateRPMBind = function (id)
  {
    var id = '#'+id;

    $(id+' button[name=solve]').click(function(){

      //Get variables
      var ratio = $(id+' input[name=ratio]').val();
      var speed = $(id+' input[name=speed]').val();
      var tireHeight = $(id+' input[name=tireHeight]').val();
      $(id+' div[name=message]').html("");

      if(!calValidation.isDecimal(ratio)){
        $(id+' div[name=message]').html("Ratio isn't decimal.");
        console.error("Ratio isn't decimal.");
        //Doing something
      }

      if(!calValidation.isDecimal(speed)){
        $(id+' div[name=message]').html("Speed isn't decimal.");
        console.error("Speed isn't decimal.");
        //Doing something
      }

      if(!calValidation.isDecimal(tireHeight)){
        $(id+' div[name=message]').html("Speed isn't decimal.");
        console.error("Tire height isn't decimal.");
        //Doing something
      }

      ratio = parseFloat(ratio);
      speed = parseFloat(speed);
      tireHeight = parseFloat(tireHeight);
      if(tireHeight===0){
        $(id+' div[name=message]').html("Tire height can't be zero.");
        console.error("Tire height can't be zero.");
        //Doing something
      }

      var RPM = CalculateRPM(ratio,speed,tireHeight);
      $(id+' input[name=auto]').val(RPM.auto);
      $(id+' input[name=aod]').val(RPM.autoOverDrive);
      $(id+' input[name=manual]').val(RPM.manual);
      $(id+' input[name=fiveSpeed]').val(RPM.fiveSpeed);
    });
  };

  var CalculateRPM = function(ratio,speed,tireHeight){
      //autoOverDrive = aod
      var auto = Math.round((ratio*speed*345)/tireHeight);
      var autoOverDrive = Math.round((ratio * speed * 336 * .7) / tireHeight);
      var manual = Math.round((ratio * speed * 336) / tireHeight);
      var fiveSpeed = Math.round((ratio * speed * 336 * .8) / tireHeight);
      auto = Math.round(auto * 100) / 100;
      autoOverDrive = Math.round(autoOverDrive * 100) / 100;
      manual = Math.round(manual * 100) / 100;
      fiveSpeed = Math.round(fiveSpeed * 100) / 100;

      return {
        auto:auto,
        autoOverDrive:autoOverDrive,
        manual:manual,
        fiveSpeed:fiveSpeed
      };

  };

  //Calculate RPM Specific
  //Input ringPinionRatio tireHeight speed transmissionRatio
  //Output RPM
  var CalculateRPMSpecificBind = function(id){
    var id = '#'+id;
    $(id+' button[name=solve]').click(function(){
      //Get variables
      var ringPinionRatio = $(id+' input[name=ringPinionRatio]').val();
      var tireHeight = $(id+' input[name=tireHeight]').val();
      var speed = $(id+' input[name=speed]').val();
      var transmissionRatio = $(id+' input[name=transmissionRatio]').val();
      $(id+' div[name=message]').html("");

      if(!calValidation.isDecimal(ringPinionRatio)){
          $(id+' div[name=message]').html("R&P isn't decimal.");
          console.error("R&P isn't decimal.");
          //Doing something
      }

      if(!calValidation.isDecimal(speed)){
          $(id+' div[name=message]').html("Speed isn't decimal.");
          console.error("Speed isn't decimal.");
          //Doing something
      }

      if(!calValidation.isDecimal(tireHeight)){
          $(id+' div[name=message]').html("Tire height isn't decimal.");
          console.error("Tire height isn't decimal.");
          //Doing something
      }

      ringPinionRatio = parseFloat(ringPinionRatio);
      tireHeight = parseFloat(tireHeight);
      speed = parseFloat(speed);
      transmissionRatio = parseFloat(transmissionRatio);

      if(tireHeight===0){
          $(id+' div[name=message]').html("Tire height can't be zero.");
          console.error("Tire height can't be zero.");
          //Doing something
      }

      var RPM = CalculateRPMSpecific(ringPinionRatio,tireHeight,speed,transmissionRatio);
      $(id+' input[name=RPM]').val(RPM);

    });
  };

  var CalculateRPMSpecific = function(ringPinionRatio,tireHeight,speed,transmissionRatio){
    return Math.round(Math.round((ringPinionRatio * speed * 336 * transmissionRatio) / tireHeight) * 100 )/ 100;
  };

  //Calculate Tire Height
  //Input width,aspect,wheel
  //Output Tire Height
  var CalculateTireHeightBind = function(id){
    var id = '#'+id;
    $(id+' button[name=solve]').click(function(){

      var width = $(id+' input[name=width]').val();
      var aspect = $(id+' input[name=aspect]').val();
      var wheel = $(id+' input[name=wheel]').val();
      $(id+' div[name=message]').html("");

      if(!calValidation.isDecimal(width)){
          $(id+' div[name=message]').html("Width isn't decimal.");
          console.error("Width isn't decimal.");
          //Doing something
      }

      if(!calValidation.isDecimal(aspect)){
          $(id+' div[name=message]').html("Aspect isn't decimal.");
          console.error("Aspect isn't decimal.");
          //Doing something
      }

      if(!calValidation.isDecimal(wheel)){
          $(id+' div[name=message]').html("Wheel isn't decimal.");
          console.error("Wheel isn't decimal.");
          //Doing something
      }

      width = parseFloat(width);
      aspect = parseFloat(aspect);
      wheel = parseFloat(wheel);

      var tireHeight = CalulateTireHeight(width,aspect,wheel);
      $(id+' input[name=tireHeight]').val(tireHeight);

    });
  }

  var CalulateTireHeight = function(width,aspect,wheel){
      return Math.round(((width / 1270) * aspect + wheel) * 100) / 100;
  }

  //// https://www.ringpinion.com/calculators/Calc_GR.aspx
  //Gear Ratio Calculator
  //Input ringGear, pinionGear
  //Output Gear Ratio
  var CalculateGearRatioBind = function(id){
    var id = '#'+id;
    $(id+' button[name=solve]').click(function(){

      var ringGear = $(id+' input[name=ringGear]').val();
      var pinionGear = $(id+' input[name=pinionGear]').val();
      $(id+' div[name=message]').html("");

      if(!calValidation.isDecimal(ringGear)){
          $(id+' div[name=message]').html("Ring Gear isn't decimal.");
          console.error("Ring Gear isn't decimal.");
          //Doing something
      }

      if(!calValidation.isDecimal(pinionGear)){
          $(id+' div[name=message]').html("Pinion Gear isn't decimal.");
          console.error("Pinion Gear isn't decimal.");
          //Doing something
      }

      ringGear = parseFloat(ringGear);
      pinionGear = parseFloat(pinionGear);

      if(pinionGear===0){
        $(id+' div[name=message]').html("Pinion Gear can't be zero.");
        console.error("Pinion Gear can't be zero.");
        //Doing something
      }

      var tireHeight = CalculateGearRatio(ringGear,pinionGear);
      $(id+' input[name=gearRatio]').val(tireHeight);

    });
  }

  var CalculateGearRatio = function(ringGear,pinionGear){
    return Math.round((ringGear / pinionGear) * 100) / 100;
  }

  //// https://www.ringpinion.com/calculators/Calc_ED.aspx
  //Engine Displacement Calculator
  //Input bore stroke cylinders units
  //Unit must set to be inches and millimeters
  //Output Engine Displacement: cubicInches cubicCentimeters

  var CalculateEngineDisplacementBind = function(id){
    var id = '#'+id;
    $(id+' button[name=solve]').click(function(){

      var boreSize = $(id+' input[name=boreSize]').val();
      var strokeLength = $(id+' input[name=strokeLength]').val();
      var cylinders = $(id+' input[name=cylinders]').val();
      var units = $(id+' input[name=units]:checked').val();
      $(id+' div[name=message]').html("");

      if(!calValidation.isDecimal(boreSize)){
          $(id+' div[name=message]').html("Bore Size isn't decimal.");
          console.error("Bore Size isn't decimal.");
          //Doing something
      }

      if(!calValidation.isDecimal(strokeLength)){
          $(id+' div[name=message]').html("Stroke Length isn't decimal.");
          console.error("Stroke Length isn't decimal.");
          //Doing something
      }

      if(!calValidation.isDecimal(cylinders)){
          $(id+' div[name=message]').html("Cylinders isn't decimal.");
          console.error("Cylinders isn't decimal.");
          //Doing something
      }

      boreSize = parseFloat(boreSize);
      strokeLength = parseFloat(strokeLength);
      cylinders = parseFloat(cylinders);
      var ed = CalculateEngineDisplacement(boreSize,strokeLength,cylinders,units);
      $(id+' input[name=cubicInches]').val(ed.displacementInInches);
      $(id+' input[name=cubicCentimeters]').val(ed.displacementInCentimeters);

    });
  }

  var CalculateEngineDisplacement = function(boreSize,strokeLength,cylinders,units){

    var radius = boreSize / 2;
    volume = Math.pow(radius, 2) * Math.PI * strokeLength * cylinders;
    var displacementInInches = 0 ,displacementInCentimeters=0;

    if(units==="inches")
    {
      displacementInInches = Math.round(volume);
      displacementInCentimeters =  Math.round(volume * 16.387064);
    }else{
      displacementInInches = Math.round((volume/ 1000) / 16.387064);
      displacementInCentimeters =   Math.round(volume/100)/10; // Math.Round(volume / 1000, 1) =>  Math.round((volume/1000)*10)/10
    }

    return {
      displacementInInches:displacementInInches,
      displacementInCentimeters:displacementInCentimeters
    };
  }

  //// https://www.ringpinion.com/Calculators/Calc_IL.aspx
  //Cubic Inches to Liters Conversion Calculator
  //Input Cubic Inches
  //Output liters

  var CalculateCubicInchesToLitersBind = function(id){
    var id = '#'+id;
    $(id+' button[name=solve]').click(function(){

      var cubicInches = $(id+' input[name=cubicInches]').val();
      $(id+' div[name=message]').html("");

      if(!calValidation.isDecimal(cubicInches)){
          $(id+' div[name=message]').html("Cubic Inches isn't decimal.");
          console.error("Cubic Inches isn't decimal.");
          //Doing something
      }

      cubicInches = parseFloat(cubicInches);
      var liters = CalculateCubicInchesToLiters(cubicInches);
      $(id+' input[name=liters]').val(liters);

    });
  }

  var CalculateCubicInchesToLiters = function(cubicInches){
      return Math.round((cubicInches / 61.01) * 1000) / 1000;
  }
  
  //Liters to Cubic Inches Conversion Calculator
  //Input Liters
  //Output Cubic Inches
  var CalculateLitersToCubicInchesBind = function(id){
    var id = '#'+id;
    $(id+' button[name=solve]').click(function(){

      var liters= $(id+' input[name=liters]').val();
      $(id+' div[name=message]').html("");

      if(!calValidation.isDecimal(liters)){
          $(id+' div[name=message]').html("Liters isn't decimal.");
          console.error("Liters isn't decimal.");
          //Doing something
      }

      cubicInches = parseFloat(cubicInches);
      var  cubicInches = CalculateLitersToCubicInches(liters);
      $(id+' input[name=cubicInches]').val(cubicInches );
    });
  }

  var CalculateLitersToCubicInches = function(cubicInches){
      return Math.round((cubicInches * 61.01) * 100) / 100;
  }

  //Rod Ratio Calculator
  //Input Rod Length ,Stroke
  //Output Rod Ratio
  var CalculateRodRatioBind = function(id){
    var id = '#'+id;
    $(id+' button[name=solve]').click(function(){

      var rodLength= $(id+' input[name=rodLength]').val();
      var stroke= $(id+' input[name=stroke]').val();
      $(id+' div[name=message]').html("");

      if(!calValidation.isDecimal(rodLength)){
          $(id+' div[name=message]').html("RodLength isn't decimal.");
          console.error("RodLength isn't decimal.");
          //Doing something
      }

      if(!calValidation.isDecimal(stroke)){
          $(id+' div[name=message]').html("RodLength isn't decimal.");
          console.error("Stroke isn't decimal.");
          //Doing something
      }

      rodLength = parseFloat(rodLength);
      stroke = parseFloat(stroke);

      if(stroke===0){
        $(id+' div[name=message]').html("Stroke can't be zero.");
        console.error("Stroke can't be zero.");
        //Doing something
      }

      var rodRatio = CalculateRodRatio(rodLength,stroke);
      $(id+' input[name=rodRatio]').val(rodRatio );

    });
  }

  var CalculateRodRatio = function(rodLength,stroke){
      return Math.round((rodLength / stroke) * 100) / 100;
  }

  //Horsepower Calculator
  //Input Torque, RPM
  //Output Horsepower
  var CalculateHorsepowerBind = function(id){
    var id = '#'+id;
    $(id+' button[name=solve]').click(function(){

      var torque = $(id+' input[name=torque]').val();
      var rpm = $(id+' input[name=rpm]').val();
      $(id+' div[name=message]').html("");

      if(!calValidation.isDecimal(torque)){
          $(id+' div[name=message]').html("Torque isn't decimal.");
          console.error("Torque isn't decimal.");
          //Doing something
      }

      if(!calValidation.isDecimal(rpm)){
          $(id+' div[name=message]').html("Torque isn't decimal.");
          console.error("Torque isn't decimal.");
          //Doing something
      }

      torque = parseFloat(torque);
      rpm = parseFloat(rpm);
      var horsepower = CalculateHorsepower(torque,rpm);
      $(id+' input[name=horsepower]').val(horsepower );

    });
  }

  var CalculateHorsepower = function(torque,rpm){
      return Math.round(((rpm * torque) / 5252) * 10) / 10;
  }

  new CalculateRPMBind('calcRPM');
  new CalculateRPMSpecificBind('calcSpecificRPM');
  new CalculateTireHeightBind('tireHeight');
  new CalculateGearRatioBind('gearRatio');
  new CalculateEngineDisplacementBind('calcEngineDisplacement');
  new CalculateCubicInchesToLitersBind('calcCubicInchesToLiters');
  new CalculateLitersToCubicInchesBind('calcLiterstoCubicInches');
  new CalculateRodRatioBind('calcRodRatio');
  new CalculateHorsepowerBind('calcHorsepower');

  /* jshint ignore:end */
})(jQuery);