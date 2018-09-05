<div class="calculator" id="calcEngineDisplacement">
  <div class="calculator__input">
    <div class="calculator__form-group">
      <label for="bore-size">Bore Size</label>
      <input type="number" class="form-control" id="bore-size" placeholder="Enter" name="boreSize" >
    </div>
    <div class="calculator__form-group">
      <label for="stroke-length">Stroke Length</label>
      <input type="number" class="form-control" id="stroke-length" placeholder="Enter" name="strokeLength">
    </div>
    <div class="calculator__form-group">
      <label for="cylenders">Cylinders</label>
      <input type="number" class="form-control" id="cylenders" placeholder="Enter" name="cylinders">
    </div>
    <div class="calculator__form-group">
      <label for="units">Units Entered</label>
      <div class="calculator__radio-group custom-controls-stacked">
        <label class="calculator__radio-label custom-control custom-radio">
          <input aria-label="Inches" type="radio" name="units" value="inches" class="custom-control-input" checked>
          <span class="calculator__radio-indicator"></span>
          <span class="calculator__radio-description">Inches</span>
        </label>
        <label class="calculator__radio-label custom-control custom-radio">
          <input aria-label="Millimeters" type="radio" name="units" value="millimeters" class="custom-control-input">
          <span class="calculator__radio-indicator"></span>
          <span class="calculator__radio-description">Millimeters</span>
        </label>
      </div>
    </div>
    <div class="calculator__form-group">
      <button class="button" name="solve">Solve</button>
    </div>
  </div>
  <div class="calculator__output">
    <div class="calculator__form-group">
      <label for="cubic-inches">Cubic Inches</label>
      <input type="text" class="form-control" id="cubic-inches" name="cubicInches" readonly>
    </div>
    <div class="calculator__form-group">
      <label for="cubic-centimeters">Cubic Centimeters</label>
      <input type="text" class="form-control" id="cubic-centimeters" name="cubicCentimeters" readonly>
    </div>
  </div>
  <div class="calculator__error">
    <div name="message"></div>
  </div>
</div>
