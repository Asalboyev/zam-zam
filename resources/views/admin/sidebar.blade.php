<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
      <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}"> <img alt="image" src="/admin/assets/img/2025-08-04 03.35.27.jpg" class="header-logo" /> <span
            class="logo-name">Zam Zam</span>
        </a>
      </div>
      <ul class="sidebar-menu">
          @auth
              @if(auth()->user()->role === 'admin')
                  <li class="dropdown">
                      <a href="{{ route('admin.dashboard') }}" class="nav-link">
                          <i data-feather="monitor"></i>
                          <span>Dashboard</span>
                      </a>
                  </li>
              @endif
          @endauth
          <li class="dropdown ">
              <a href="{{ route('admin.orders.index') }}" class="nav-link"><i data-feather="shopping-cart"></i><span> Buyurtma yaratish </span></a>
          </li>
          <li class="dropdown ">
              <a href="{{ route('admin.orders.all', ['order_date' => now()->format('Y-m-d')]) }}" class="nav-link"><i data-feather="shopping-cart"></i><span>Buyurtmalar </span></a>
          </li>
          <li class="dropdown ">
              <a href="{{ route('admin.ordinary_debt',['order_date' => now()->format('Y-m-d')]) }}" class="nav-link"><i data-feather="users"></i><span> Qarzdorlar </span></a>
          </li>

          <li class="dropdown ">
              <a href="{{ route('admin.daily_meal.index') }}" class="nav-link"><i data-feather="arrow-up-circle"></i><span>Kunlik maxsulot</span></a>
          </li>
          <li class="dropdown ">
              <a href="{{ route('admin.products.index') }}" class="nav-link"><i data-feather="database"></i><span>Malumotlar</span></a>
          </li>

        <li class="dropdown ">
            <a href="{{ route('admin.customers.index') }}" class="nav-link"><i data-feather="users"></i><span>Mijozlar</span></a>
         </li>
{{--          <li class="dropdown ">--}}
{{--            <a href="{{ route('admin.drivers.index') }}" class="nav-link"><i data-feather="truck"></i><span> Haydovchilar</span></a>--}}
{{--         </li>--}}
      </ul>
    </aside>
  </div>

