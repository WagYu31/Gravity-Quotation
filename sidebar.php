      <!-- Sidebar -->
      <div class="sidebar" data-background-color="dark">
          <div class="sidebar-logo">
              <!-- Logo Header -->
              <div class="logo-header" data-background-color="dark">
                  <a href="products.php" class="logo">
                      <img
                          src="assets/img/grav.png"
                          alt="navbar brand"
                          class="navbar-brand"
                          height="25" />
                  </a>
                  <div class="nav-toggle">
                      <button class="btn btn-toggle toggle-sidebar">
                          <i class="gg-menu-right"></i>
                      </button>
                      <button class="btn btn-toggle sidenav-toggler">
                          <i class="gg-menu-left"></i>
                      </button>
                  </div>
                  <button class="topbar-toggler more">
                      <i class="gg-more-vertical-alt"></i>
                  </button>
              </div>
              <!-- End Logo Header -->
          </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
            <div class="sidebar-content">
                <ul class="nav nav-secondary">
                    <?php if ($user_id != 10 && $user_id != 11) : ?>
                    <li class="nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarLayoutsQuo">
                            <i class="fas fa-file-alt"></i>
                            <p>Quotations</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse" id="sidebarLayoutsQuo">
                            <ul class="nav nav-collapse">
                                <li>
                                    <a href="newQuotation.php">
                                        <span class="sub-item">Create Quotation</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="listQuotation.php">
                                        <span class="sub-item">Quotations</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
        
                    <li class="nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarLayouts">
                            <i class="fas fa-boxes"></i>
                            <p>Products</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse" id="sidebarLayouts">
                            <ul class="nav nav-collapse">
                                <li>
                                    <a href="products.php">
                                        <span class="sub-item">Products</span>
                                    </a>
                                </li>
                                <?php if ($user_id != 10 && $user_id != 11 && $user_id != 6 && $user_id != 9) : ?>
                                    <li>
                                        <a href="add-product.php">
                                            <span class="sub-item">Add Product</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
        
                    <li class="nav-item">
                        <a href="quoProgress.php">
                            <i class="fas fa-tasks"></i>
                            <p>Progress</p>
                        </a>
                    </li>
        
                    <li class="nav-item">
                        <a href="get_activities.php">
                            <i class="fas fa-cogs"></i>
                            <p>Request Teknisi</p>
                        </a>
                    </li>
                    
                    <?php if ($user_id != 10 && $user_id != 11) : ?>
                        <li class="nav-item">
                            <a href="customers.php">
                                <i class="fas fa-users"></i>
                                <p>Customers</p>
                            </a>
                        </li>
                    <?php endif; ?>
        
                    <?php if ($user_role == 'superadmin') : ?>
                        <li class="nav-item">
                            <a href="users.php">
                                <i class="fas fa-user-cog"></i>
                                <p>Users</p>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="ms-4">
                    <a href="https://quo.grav-tech.com/quo/dashboard.php" target="_blank" class="btn btn-sm btn-outline-light ms-2">V 2.0</a>
                    <a href="https://quo.grav-tech.com/listQuotation.php" target="_blank" class="btn btn-sm btn-light">V 1.0</a>
                </div>
            </div>
        </div>

      </div>
      <!-- End Sidebar -->