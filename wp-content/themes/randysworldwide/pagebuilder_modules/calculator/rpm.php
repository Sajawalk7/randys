<div class="calculator" id="calcRPM">
  <div class="calculator__input">
    <div class="calculator__form-group">
      <label for="ratio">Ratio</label>
      <input type="number" class="form-control" id="ratio" placeholder="Enter" name="ratio" >
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
      <button class="button" name="solve">Solve</button>
    </div>
  </div>
  <div class="calculator__output">
    <div class="calculator__form-group">
      <label for="auto">Auto*</label>
      <input type="text" class="form-control" id="auto" name="auto" readonly>
    </div>
    <div class="calculator__form-group">
      <label for="aod">AOD*</label>
      <input type="text" class="form-control" id="aod" name="aod" readonly>
    </div>
    <div class="calculator__form-group">
      <label for="manual">Manual*</label>
      <input type="text" class="form-control" id="manual" name="manual" readonly>
    </div>
    <div class="calculator__form-group">
      <label for="five-speed">5 Speed*</label>
      <input type="text" class="form-control" id="five-speed" name="fiveSpeed" readonly>
    </div>
  </div>
  <div class="calculator__error">
    <div name="message"></div>
  </div>
</div>
