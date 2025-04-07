# api/models.py

from django.db import models
from django.contrib.auth.models import AbstractBaseUser , BaseUserManager, PermissionsMixin

class UserManager(BaseUserManager):
    def create_user(self, email, password=None, **extra_fields):
        if not email:
            raise ValueError('The Email field must be set')
        email = self.normalize_email(email)
        user = self.model(email=email, **extra_fields)
        user.set_password(password)
        user.save(using=self._db)
        return user

    def create_superuser(self, email, password=None, **extra_fields):
        extra_fields.setdefault('is_staff', True)
        extra_fields.setdefault('is_superuser', True)
        return self.create_user(email, password, **extra_fields)

class User(AbstractBaseUser , PermissionsMixin):
    email = models.EmailField(unique=True)
    first_name = models.CharField(max_length=30)
    last_name = models.CharField(max_length=30)
    patronymic = models.CharField(max_length=100)
    birth_date = models.DateField()

    is_active = models.BooleanField(default=True)
    is_staff = models.BooleanField(default=False)

    objects = UserManager()

    USERNAME_FIELD = 'email'
    REQUIRED_FIELDS = ['first_name', 'last_name', 'patronymic', 'birth_date']

class LaunchSite(models.Model):
    name = models.CharField(max_length=255)
    latitude = models.FloatField()
    longitude = models.FloatField()

    def __str__(self):
        return self.name

class LandingSite(models.Model):
    name = models.CharField(max_length=100)
    latitude = models.FloatField()
    longitude = models.FloatField()

class CrewMember(models.Model):
    name = models.CharField(max_length=100)
    role = models.CharField(max_length=100)

class Spacecraft(models.Model):
    command_module = models.CharField(max_length=100)
    lunar_module = models.CharField(max_length=100)
    crew = models.ManyToManyField(CrewMember)

class LunarMission(models.Model):
    name = models.CharField(max_length=100)
    launch_date = models.DateField()
    landing_date = models.DateField()
    launch_site = models.ForeignKey(LaunchSite, on_delete=models.CASCADE)
    landing_site = models.ForeignKey(LandingSite, on_delete=models.CASCADE)
    spacecraft = models.ForeignKey(Spacecraft, on_delete=models.CASCADE)

    def __str__(self):
        return self.name

class SpaceFlight(models.Model):
    flight_number = models.CharField(max_length=20, unique=True)
    destination = models.CharField(max_length=100)
    launch_date = models.DateField()
    seats_available = models.IntegerField()

    def __str__(self):
        return self.flight_number

