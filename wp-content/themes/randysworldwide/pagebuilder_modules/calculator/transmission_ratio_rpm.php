<div class="calculator" id="calcSpecificRPM">
  <div class="calculator__input">
    <div class="calculator__form-group">
      <label for="ring-pinion-ratio">R&P Ratio</label>
      <input type="number" class="form-control" id="ring-pinion-ratio" placeholder="Enter" name="ringPinionRatio" >
    </div>
    <div class="calculator__form-group">
      <label for="tire-height">Tire Height</label>
      <input type="number" class="form-control" id="tire-height" placeholder="Enter" name="tireHeight">
    </div>
    <div class="calculator__form-group">
      <label for="speed">Speed</label>
      <input type="number" class="form-control" id="speed" placeholder="Enter" name="speed">
    </div>
    <div class="calculator__form-group">
      <label for="transmission-ratio">Trans Ratio</label>
      <input type="number" class="form-control" id="transmission-ratio" placeholder="Enter" name="transmissionRatio">
    </div>
    <div class="calculator__form-group">
      <button class="button" name="solve">Solve</button>
    </div>
  </div>
  <div class="calculator__output">
    <div class="calculator__form-group">
      <label for="rpm">RPM</label>
      <input type="text" class="form-control" id="rpm" name="RPM" readonly>
    </div>
  </div>
  <div class="calculator__error">
    <div name="message"></div>
  </div>
</div>
