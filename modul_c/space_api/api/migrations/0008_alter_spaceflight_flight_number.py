# Generated by Django 4.2.19 on 2025-02-09 15:29

from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('api', '0007_alter_launchsite_name'),
    ]

    operations = [
        migrations.AlterField(
            model_name='spaceflight',
            name='flight_number',
            field=models.CharField(max_length=20, unique=True),
        ),
    ]
