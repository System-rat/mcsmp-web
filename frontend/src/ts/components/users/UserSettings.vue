<template>
    <div>
        <div class="container shadow-sm border">
            <div class="row">
                <div class="col-3 border-right">
                    <h5> User settings </h5>
                    <ul class="nav nav-pills flex-column">
                        <div class="dropdown-divider"></div>
                        <li class="nav-item">
                            <router-link class="nav-link" active-class="active" to="/account/general"> General </router-link>
                        </li>
                    </ul>
                </div>
                <div class="col-9">
                    <router-view></router-view>
                </div>
            </div>
        </div>
    </div>
</template>