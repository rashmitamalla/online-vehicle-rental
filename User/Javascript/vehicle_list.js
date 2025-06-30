
document.addEventListener("DOMContentLoaded", function () {
  const filterForm = document.getElementById("filter-form");
  const dateForm = document.getElementById("date-filter-form");
  const vehicleList = document.querySelector(".vehicle-list");
  const resetBtn = document.getElementById("reset-btn");

  const pickupDateInput = document.querySelector("input[name='pickup_date']");
  const pickupTimeInput = document.querySelector("input[name='pickup_time']");
  const returnDateInput = document.querySelector("input[name='return_date']");
  const returnTimeInput = document.querySelector("input[name='return_time']");

  const now = new Date();
  const minDateStr = now.toISOString().split("T")[0];
  pickupDateInput.min = minDateStr;
  returnDateInput.min = minDateStr;

  const minSlider = document.getElementById('range-min');
  const maxSlider = document.getElementById('range-max');
  const minPrice = document.getElementById('min-price');
  const maxPrice = document.getElementById('max-price');
  const hiddenMin = document.getElementById('hidden-min-price');
  const hiddenMax = document.getElementById('hidden-max-price');

  function updatePrices(triggerFetch = true) {
    let minVal = parseInt(minSlider.value);
    let maxVal = parseInt(maxSlider.value);

    if (minVal > maxVal - 1000) minVal = maxVal - 1000;
    if (maxVal < minVal + 1000) maxVal = minVal + 1000;

    minSlider.value = minVal;
    maxSlider.value = maxVal;
    minPrice.textContent = `${minVal}`;
    maxPrice.textContent = `${maxVal}`;
    hiddenMin.value = minVal;
    hiddenMax.value = maxVal;

    if (triggerFetch) sendFilterData(); // auto-fetch on price change
  }

  minSlider.addEventListener("input", () => updatePrices(true));
  maxSlider.addEventListener("input", () => updatePrices(true));
  updatePrices(false); // on page load

  // ✅ Function to fetch data
  function sendFilterData() {
    const formData = new FormData(filterForm);
    const dateData = new FormData(dateForm);
    dateData.forEach((value, key) => formData.append(key, value));

    fetch("filter_vehicles.php", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.text())
      .then((data) => {
        vehicleList.innerHTML = data;
      })
      .catch((err) => {
        console.error("Failed to fetch vehicles", err);
      });
  }

  // ✅ Date form validation
  function validateDateForm(e) {
    const pickupDate = pickupDateInput.value;
    const pickupTime = pickupTimeInput.value;
    const returnDate = returnDateInput.value;
    const returnTime = returnTimeInput.value;

    if (!pickupDate || !pickupTime || !returnDate || !returnTime) return true;

    const pickup = new Date(`${pickupDate}T${pickupTime}`);
    const ret = new Date(`${returnDate}T${returnTime}`);
    const now = new Date();
    const diffHours = (ret - pickup) / (1000 * 60 * 60);

    if (pickup < now) {
      alert("Pickup time cannot be in the past.");
      e.preventDefault();
      return false;
    }

    if (ret <= pickup) {
      alert("Return time must be after pickup time.");
      e.preventDefault();
      return false;
    }

    if (diffHours < 2) {
      alert("Booking duration must be at least 2 hours.");
      e.preventDefault();
      return false;
    }

    return true;
  }

  filterForm.addEventListener("submit", function (e) {
    e.preventDefault();
    if (validateDateForm(e)) sendFilterData();
  });

  dateForm.addEventListener("submit", function (e) {
    e.preventDefault();
    if (validateDateForm(e)) sendFilterData();
  });

  resetBtn.addEventListener("click", function () {
  // Clear all checkboxes
  filterForm.querySelectorAll("input[type='checkbox']").forEach(cb => cb.checked = false);

  // Reset price sliders
  minSlider.value = 1000;
  maxSlider.value = 50000;
  updatePrices(false); // This will update display but not auto-fetch yet

  // Clear date/time inputs
  dateForm.querySelectorAll("input[type='date'], input[type='time']").forEach(input => input.value = "");

  // Submit with defaults
  sendFilterData();
});

  // 🔁 Auto-update on checkbox change for category & fuel type
  document.querySelectorAll("input[type='checkbox']").forEach((box) => {
    box.addEventListener("change", () => {
      sendFilterData();
    });
  });

  // 🚀 Initial load
  sendFilterData();
});
