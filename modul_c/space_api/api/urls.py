# api/urls.py

from django.urls import path, include
from rest_framework.routers import DefaultRouter
from .views import UserRegistrationView, CustomTokenObtainPairView, LogoutView, GagarinFlightInfoView, SpaceFlightCreateView, LunarMissionInfoView, SpaceFlightCreateView, BookFlightView, SearchView, LunarWatermarkView

router = DefaultRouter()

urlpatterns = [
    path('registration/', UserRegistrationView.as_view(), name='registration'),  # Регистрация
    path('authorization/', CustomTokenObtainPairView.as_view(), name='token_obtain_pair'),  # Аутентификация
    path('logout/', LogoutView.as_view(), name='logout'),  # Выход
    path('api/gagarin-flight/', GagarinFlightInfoView.as_view(), name='gagarin_info'),  # Информация о Гагарине
    path('flight/', SpaceFlightCreateView.as_view(), name='flight_info'),
    path('lunar-missions/', LunarMissionInfoView.as_view(), name='lunar_missions'),  # Получение информации о лунных миссиях
    path('lunar-missions/<int:mission_id>/', LunarMissionInfoView.as_view(), name='lunar-mission-detail'),  # Для DELETE
    path('space-flights/', SpaceFlightCreateView.as_view(), name='space-flight-create'),
    path('book-flight/', BookFlightView.as_view(), name='book-flight'),  # Новый маршрут для бронирования
    path('search/', SearchView.as_view(), name='search'),
    path('lunar-watermark/', LunarWatermarkView.as_view(), name='lunar-watermark'),
    path('', include(router.urls)),  # Включаем маршруты из маршрутизатора
]