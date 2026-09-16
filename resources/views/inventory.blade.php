<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>Inventory Manager</title>

    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    />

    <link rel="stylesheet" href="{{ asset('css/inventory.css') }}" />

    <script src="{{ asset('js/inventory.js') }}" defer></script>
  </head>

  <body>
    <header class="bg-white border-bottom">
      <div class="container py-3">
        <span class="fw-bold fs-5">Inventory Manager</span>
      </div>
    </header>

    <main class="container py-4 py-lg-5">
      <div class="mb-4">
        <h1 class="fw-bold">Product inventory</h1>
        <p class="text-secondary mb-0">
          Add products, update stock, and keep track of their total value.
        </p>
      </div>

      <div
        id="notice"
        class="alert d-none"
        role="status"
        aria-live="polite"
      ></div>

      <noscript>
        <div class="alert alert-warning">
          Enable JavaScript to load and update inventory.
        </div>
      </noscript>

      <section class="card shadow-sm mb-4" aria-labelledby="form-heading">
        <div class="card-body p-4">
          <h2 id="form-heading" class="h5 fw-bold mb-4">Add a product</h2>

          <form
            id="product-form"
            action="{{ route('products.store') }}"
            data-list-url="{{ route('products.index') }}"
            novalidate
          >
            <div class="row g-3">
              <div class="col-md-6">
                <label for="name" class="form-label">Product name</label>

                <input
                  id="name"
                  name="name"
                  type="text"
                  class="form-control"
                  maxlength="120"
                  aria-describedby="name-error"
                  required
                />

                <div id="name-error" class="invalid-feedback"></div>
              </div>

              <div class="col-md-3">
                <label for="quantity" class="form-label">
                  Quantity in stock
                </label>

                <input
                  id="quantity"
                  name="quantity"
                  type="text"
                  inputmode="numeric"
                  class="form-control"
                  placeholder="0"
                  aria-describedby="quantity-error"
                  required
                />

                <div id="quantity-error" class="invalid-feedback"></div>
              </div>

              <div class="col-md-3">
                <label for="price" class="form-label">Price per item</label>

                <input
                  id="price"
                  name="price"
                  type="text"
                  inputmode="decimal"
                  class="form-control"
                  placeholder="0.00"
                  aria-describedby="price-error"
                  required
                />

                <div id="price-error" class="invalid-feedback"></div>
              </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
              <button
                id="save-button"
                type="submit"
                class="btn btn-primary px-4"
                disabled
              >
                Add product
              </button>

              <button
                id="cancel-button"
                type="button"
                class="btn btn-outline-secondary d-none"
              >
                Cancel edit
              </button>
            </div>
          </form>
        </div>
      </section>

      <section class="card shadow-sm" aria-labelledby="list-heading">
        <div
          class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3"
        >
          <div>
            <h2 id="list-heading" class="h5 fw-bold mb-1">Products</h2>
            <p class="small text-secondary mb-0">
              Oldest submissions first. Dates use your local time.
            </p>
          </div>

          <div class="d-flex align-items-center gap-3">
            <span id="product-count" class="badge product-count">
              0 products
            </span>

            <button
              id="reload-button"
              type="button"
              class="btn btn-sm btn-outline-secondary"
            >
              Reload list
            </button>
          </div>
        </div>

        <div
          class="table-responsive"
          tabindex="0"
          role="region"
          aria-labelledby="list-heading"
        >
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th scope="col">Product name</th>
                <th scope="col" class="text-end">Quantity in stock</th>
                <th scope="col" class="text-end">Price per item</th>
                <th scope="col">Datetime submitted</th>
                <th scope="col" class="text-end">Total value number</th>
                <th scope="col">
                  <span class="visually-hidden">Actions</span>
                </th>
              </tr>
            </thead>

            <tbody id="product-rows">
              <tr>
                <td colspan="6" class="text-center text-secondary py-5">
                  Loading products…
                </td>
              </tr>
            </tbody>

            <tfoot>
              <tr>
                <th scope="row" colspan="4">Sum total</th>
                <td id="grand-total" class="text-end fw-bold">0.00</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </section>
    </main>
  </body>
</html>