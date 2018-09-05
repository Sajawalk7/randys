<div class="calculator" id="calcHorsepower">
  <div class="calculator__input">
    <div class="calculator__form-group">
      <label for="torque">Torque</label>
      <input type="number" class="form-control" id="torque" placeholder="Enter" name="torque" >
    </div>
    <div class="calculator__form-group">
      <label for="rpm">RPM</label>
      <input type="number" class="form-control" id="rpm" placeholder="Enter" name="rpm">
    </div>
    <div class="calculator__form-group">
      <button class="button" name="solve">Solve</button>
    </div>
  </div>
  <div class="calculator__output">
    <div class="calculator__form-group">
      <label for="horsepower">Horsepower</label>
      <input type="text" class="form-control" id="horsepower" name="horsepower" readonly>
    </div>
  </div>
  <div class="calculator__error">
    <div name="message"></div>
  </div>
</div>
