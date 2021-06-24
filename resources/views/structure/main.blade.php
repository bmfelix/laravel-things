<!DOCTYPE html>
<html lang="en">
    @include('structure.head')
    <body>
        <!-- Responsive navbar-->
        @include('structure.header')
        <!-- Page content-->
        @yield('content')
        @include('structure.footer')
    </body>
</html>
