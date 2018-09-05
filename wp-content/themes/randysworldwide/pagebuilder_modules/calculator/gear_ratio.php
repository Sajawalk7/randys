<div class="calculator" id="gearRatio">
  <div class="calculator__input">
    <div class="calculator__form-group">
      <label for="ring-gear">Ring Gear</label>
      <input type="number" class="form-control" id="ring-gear" placeholder="Enter" name="ringGear" >
    </div>
    <div class="calculator__form-group">
      <label for="pinion-gear">Pinion Gear</label>
      <input type="number" class="form-control" id="pinion-gear" placeholder="Enter" name="pinionGear">
    </div>
    <div class="calculator__form-group">
      <button class="button" name="solve">Solve</button>
    </div>
  </div>
  <div class="calculator__output">
    <div class="calculator__form-group">
      <label for="gear-ratio">Gear Ratio</label>
      <input type="text" class="form-control" id="gear-ratio" name="gearRatio" readonly>
    </div>
  </div>
  <div class="calculator__error">
    <div name="message"></div>
  </div>
</div>
