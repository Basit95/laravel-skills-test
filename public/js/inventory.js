const form = document.querySelector("#product-form");
const rows = document.querySelector("#product-rows");
const heading = document.querySelector("#form-heading");
const notice = document.querySelector("#notice");
const saveButton = document.querySelector("#save-button");
const cancelButton = document.querySelector("#cancel-button");
const reloadButton = document.querySelector("#reload-button");
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const fieldNames = ["name", "quantity", "price"];

let products = [];
let editingId = null;
let loading = false;
let ready = false;

function formatMoney(cents) {
  const whole = Math.floor(cents / 100).toLocaleString("en-US");
  const fraction = String(cents % 100).padStart(2, "0");

  return `${whole}.${fraction}`;
}

function showNotice(text, type = "success") {
  notice.textContent = text;
  notice.className = `alert alert-${type}`;
  notice.setAttribute("role", type === "danger" ? "alert" : "status");
}

function setLoading(value) {
  loading = value;
  saveButton.disabled = value || !ready;
  cancelButton.disabled = value;
  reloadButton.disabled = value;

  for (const name of fieldNames) {
    form.elements.namedItem(name).disabled = value;
  }

  for (const button of rows.querySelectorAll("button")) {
    button.disabled = value;
  }

  saveButton.textContent = value
    ? "Please wait…"
    : editingId
      ? "Save changes"
      : "Add product";

  form.setAttribute("aria-busy", String(value));
}

function clearErrors() {
  for (const name of fieldNames) {
    const input = form.elements.namedItem(name);

    input.classList.remove("is-invalid");
    input.removeAttribute("aria-invalid");

    document.querySelector(`#${name}-error`).textContent = "";
  }
}

function resetForm() {
  editingId = null;
  form.reset();
  clearErrors();

  heading.textContent = "Add a product";
  saveButton.textContent = "Add product";
  cancelButton.classList.add("d-none");

  for (const row of rows.children) {
    row.classList.remove("editing");
  }
}

async function request(url, method = "GET", values = null) {
  const options = {
    method,
    credentials: "same-origin",
    cache: "no-store",
    headers: {
      Accept: "application/json",
      "X-CSRF-TOKEN": csrfToken,
    },
  };

  if (values !== null) {
    options.headers["Content-Type"] = "application/json";
    options.body = JSON.stringify(values);
  }

  const response = await fetch(url, options);
  const data = await response.json().catch(() => null);

  if (!response.ok) {
    let message = data?.message || "The request failed. Please try again.";

    if (response.status === 419) {
      message = "Your session expired. Refresh the page and try again.";
    } else if (response.status >= 500) {
      message = "The inventory could not be loaded or saved. Please try again.";
    }

    const error = new Error(message);
    error.fields = data?.errors || {};

    throw error;
  }

  if (!data || !Array.isArray(data.products)) {
    throw new Error("The server returned an unexpected response.");
  }

  return data;
}

function addCell(row, text, className = "") {
  const cell = row.insertCell();

  cell.textContent = text;
  cell.className = className;

  return cell;
}

function renderProducts(data) {
  products = data.products;
  rows.replaceChildren();

  document.querySelector("#grand-total").textContent = formatMoney(
    data.total_cents,
  );

  document.querySelector("#product-count").textContent =
    `${products.length} product${products.length === 1 ? "" : "s"}`;

  if (products.length === 0) {
    const row = rows.insertRow();
    const cell = addCell(
      row,
      "No products yet. Add your first product above.",
      "text-center text-secondary py-5",
    );

    cell.colSpan = 6;

    return;
  }

  for (const product of products) {
    const row = rows.insertRow();

    addCell(row, product.name);
    addCell(row, product.quantity.toLocaleString("en-US"), "text-end");
    addCell(row, formatMoney(product.price_cents), "text-end");

    const time = document.createElement("time");

    time.dateTime = product.submitted_at;
    time.textContent = new Date(product.submitted_at).toLocaleString("en-GB", {
      year: "numeric",
      month: "short",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false,
    });

    row.insertCell().append(time);

    addCell(
      row,
      formatMoney(product.total_cents),
      "text-end fw-semibold",
    );

    const editButton = document.createElement("button");

    editButton.type = "button";
    editButton.className = "btn btn-sm btn-outline-secondary";
    editButton.textContent = "Edit";
    editButton.dataset.id = product.id;
    editButton.setAttribute("aria-label", `Edit ${product.name}`);

    row.insertCell().append(editButton);
  }
}

async function loadProducts() {
  if (loading) {
    return;
  }

  setLoading(true);

  try {
    const data = await request(form.dataset.listUrl);

    resetForm();
    renderProducts(data);

    ready = true;
    notice.classList.add("d-none");
  } catch (error) {
    showNotice(error.message, "danger");

    if (!ready) {
      rows.querySelector("td").textContent =
        "Products could not be loaded. Click Reload list to try again.";
    }
  } finally {
    setLoading(false);
  }
}

form.addEventListener("submit", async (event) => {
  event.preventDefault();

  if (loading || !ready) {
    return;
  }

  clearErrors();

  const values = {};

  for (const name of fieldNames) {
    values[name] = form.elements.namedItem(name).value.trim();
  }

  const updating = editingId !== null;
  const url = updating ? `${form.action}/${editingId}` : form.action;

  setLoading(true);

  try {
    const data = await request(
      url,
      updating ? "PUT" : "POST",
      values,
    );

    resetForm();
    renderProducts(data);

    showNotice(updating ? "Product updated." : "Product added.");
  } catch (error) {
    showNotice(error.message, "danger");

    for (const [name, messages] of Object.entries(error.fields || {})) {
      if (!fieldNames.includes(name)) {
        continue;
      }

      const input = form.elements.namedItem(name);

      input.classList.add("is-invalid");
      input.setAttribute("aria-invalid", "true");

      document.querySelector(`#${name}-error`).textContent = messages[0];
    }
  } finally {
    setLoading(false);

    const firstInvalid = form.querySelector(".is-invalid");
    const nextInput = firstInvalid || form.elements.namedItem("name");

    nextInput.focus();
  }
});

rows.addEventListener("click", (event) => {
  const button = event.target.closest("button[data-id]");

  if (!button || loading) {
    return;
  }

  const product = products.find((item) => item.id === button.dataset.id);

  if (!product) {
    return;
  }

  resetForm();
  editingId = product.id;

  for (const name of fieldNames) {
    form.elements.namedItem(name).value = product[name];
  }

  heading.textContent = "Edit product";
  saveButton.textContent = "Save changes";
  cancelButton.classList.remove("d-none");
  notice.classList.add("d-none");

  button.closest("tr").classList.add("editing");

  form.scrollIntoView({
    behavior: "auto",
    block: "center",
  });

  form.elements.namedItem("name").focus();
});

cancelButton.addEventListener("click", () => {
  resetForm();
  form.elements.namedItem("name").focus();
});

reloadButton.addEventListener("click", loadProducts);

loadProducts();